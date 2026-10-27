<?php

namespace App\Services;

use App\Jobs\EnvoyerFactureWhatsAppJob;
use App\Models\Commande;
use App\Models\Facture;
use App\Models\Utilisateur;
use App\Models\WhatsappEnvoi;
use App\Notifications\PaiementCommandeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Étapes 2.1 / 2.2 — Orchestration du flux de facturation (acompte / solde).
 *
 * Les montants sont figés : dès le premier versement, le prix est verrouillé
 * et le solde calculé automatiquement. L'encaissement du solde n'accepte que
 * le montant exact restant — aucune saisie manuelle possible.
 */
class FactureService
{
    public const MODULE = 'facture';

    public function __construct(
        private readonly int $superviseurId,
        private readonly string $module,
        private readonly ManagerAuthService $managerAuth = new ManagerAuthService,
    ) {}

    /* ────────────── Étape 2.1 — Saisie initiale (acompte) ────────────── */

    /**
     * Crée la commande + facture d'acompte via le nouveau formulaire.
     *
     * Champs : type, montant (total), acompte (versement initial obligatoire),
     * client_nom (facultatif), numero_whatsapp (obligatoire, format +237...),
     * details (facultatif).
     */
    /** Le montant donne-t-il lieu à une facture ? (> seuil configuré, 1 000 FCFA). */
    public static function doitFacturer(float $montant): bool
    {
        return $montant > (float) config('facture.montant_minimum', 1000);
    }

    public function creerAcompte(Request $request): Commande
    {
        $montant = round((float) $request->montant, 2);
        $facturable = self::doitFacturer($montant);

        // +237 par défaut : on normalise le numéro AVANT validation pour éviter
        // l'erreur « format invalide » quand l'utilisateur oublie l'indicatif pays.
        if ($request->filled('numero_whatsapp')) {
            $request->merge([
                'numero_whatsapp' => self::normaliserTelephone((string) $request->input('numero_whatsapp')),
            ]);
        }

        $request->validate([
            'type' => 'required|string',
            'montant' => 'required|numeric|min:1|max:100000000',
            'acompte' => $facturable
                ? 'required|numeric|min:1|max:100000000'
                : 'nullable|numeric|min:0|max:100000000',
            'client_nom' => 'nullable|string|max:100',
            'numero_whatsapp' => $facturable
                ? ['required', 'string', 'regex:'.config('facture.whatsapp_regex')]
                : ['nullable', 'string', 'regex:'.config('facture.whatsapp_regex')],
            'details' => 'nullable|string|max:1000',
        ]);

        // Montants <= 1 000 FCFA : vente simple, sans facture PDF ni WhatsApp
        $acompte = round((float) ($request->acompte ?? $montant), 2);

        if ($acompte > $montant) {
            throw ValidationException::withMessages([
                'acompte' => "L'acompte (".number_format($acompte, 0, ',', '.').') ne peut pas dépasser le montant total ('.number_format($montant, 0, ',', '.').').',
            ]);
        }

        // Acompte minimal : au moins X% du montant total sur une commande facturée.
        if ($facturable) {
            $pct = (float) config('facture.acompte_minimum_pct', 35);
            $mini = round($montant * $pct / 100, 2);
            if ($acompte < $mini) {
                throw ValidationException::withMessages([
                    'acompte' => "L'acompte doit être supérieur ou égal à {$pct}% du montant total, soit au minimum "
                        .number_format($mini, 0, ',', '.').' FCFA.',
                ]);
            }
        }

        $comptant = $acompte >= $montant;

        $commande = DB::transaction(function () use ($request, $montant, $acompte, $comptant, $facturable) {
            $numero = $facturable ? self::genererNumero() : null;

            $commande = Commande::create([
                'superviseur_id' => $this->superviseurId,
                'type' => $request->type,
                'montant' => $montant,
                'montant_paye' => $acompte,
                'statut_paiement' => $comptant ? 'paye' : Commande::STATUT_EN_ATTENTE_SOLDE,
                'details' => $request->details,
                'date' => now()->toDateString(),
                'numero_whatsapp' => $request->filled('numero_whatsapp')
                    ? self::normaliserTelephone($request->numero_whatsapp)
                    : null,
                'client_nom' => $request->client_nom,
                'numero_facture' => $numero,
                'prix_verrouille' => $facturable, // verrou dès le premier versement
            ]);

            if ($facturable) {
                FacturePdfService::figer(
                    $commande,
                    $numero,
                    $comptant ? Facture::PHASE_SOLDE : Facture::PHASE_ACOMPTE
                );
            }

            return $commande;
        });

        AuditService::created(
            $this->module,
            $facturable
                ? "Facture {$commande->numero_facture} — acompte ".number_format($acompte, 0, ',', '.').
                    ' FCFA sur '.number_format($montant, 0, ',', '.')." FCFA (WhatsApp {$commande->numero_whatsapp})"
                : "Vente {$request->type} de ".number_format($montant, 0, ',', '.').' FCFA (sans facture, montant ≤ '.number_format((float) config('facture.montant_minimum', 1000), 0, ',', '.').' FCFA)',
            $commande->toArray(),
            $request
        );

        // Étape 4.2 — envoi WhatsApp automatique dès la confirmation du paiement
        if ($facturable) {
            $this->envoyerWhatsApp($commande->numero_facture, Facture::PHASE_ACOMPTE);
        }

        return $commande;
    }

    /* ────────────── Étape 2.2 — Clôture (solde) ────────────── */

    /**
     * Encaisse le solde exact d'une commande en attente.
     *
     * Verrou (Étape 2.2) : le champ reste à payer n'accepte AUCUN montant —
     * le serveur calcule le reste et bascule la facture à "Payée".
     */
    public function encaisserSolde(int $commandeId, ?Request $request = null): array
    {
        $commande = Commande::where('id', $commandeId)
            ->where('superviseur_id', $this->superviseurId)
            ->firstOrFail();

        if ($commande->statut_paiement === 'paye') {
            $label = $commande->numero_facture ? "La facture {$commande->numero_facture}" : "La vente #{$commande->id}";

            return ['success' => false, 'error' => "{$label} est déjà payée."];
        }
        if (in_array($commande->statut_paiement, ['annule'], true)) {
            return ['success' => false, 'error' => 'Commande annulée — encaissement impossible.'];
        }

        $solde = round($commande->solde, 2);

        $ancien = $commande->toArray();
        $commande->update([
            'montant_paye' => round((float) $commande->montant, 2),
            'statut_paiement' => 'paye',
        ]);

        // Facture solde uniquement si la vente a été facturée (> seuil)
        if ($commande->numero_facture) {
            FacturePdfService::figer($commande, $commande->numero_facture, Facture::PHASE_SOLDE);
        }

        AuditService::updated(
            $this->module,
            "Encaissement du solde — facture {$commande->numero_facture} : {$solde} FCFA → PAYÉE",
            $ancien,
            $commande->toArray(),
            $request
        );

        try {
            $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
            if ($admin) {
                $admin->notify(new PaiementCommandeNotification($commande, $solde, 'paye'));
            }
        } catch (\Throwable $e) {
            Log::warning('Notification paiement échouée: '.$e->getMessage());
        }

        if ($commande->numero_facture) {
            $this->envoyerWhatsApp($commande->numero_facture, Facture::PHASE_SOLDE);
        }

        return [
            'success' => true,
            'message' => ($commande->numero_facture ? "Facture {$commande->numero_facture}" : "Vente #{$commande->id}").' payée — solde '.
                number_format($solde, 0, ',', '.').' FCFA encaissé.',
        ];
    }

    /* ────────────── Verrou manager (Étape 1.2) ────────────── */

    /**
     * Vérifie l'autorisation manager sur une commande verrouillée.
     *
     * @return array{authorized: bool, gestionnaire: ?Utilisateur}
     */
    public function autoriserMouvement(Commande $commande, string $action, ?string $motDePasseManager): array
    {
        if (! $commande->estVerrouillee()) {
            return ['authorized' => true, 'gestionnaire' => null];
        }

        $gestionnaire = $this->managerAuth->valider($motDePasseManager);

        $this->managerAuth->tracer(
            "caisse.{$action}",
            "{$action} sur la facture verrouillée {$commande->numero_facture}",
            $gestionnaire,
            Auth::user()
        );

        return ['authorized' => $gestionnaire !== null, 'gestionnaire' => $gestionnaire];
    }

    /* ────────────── Aides ────────────── */

    /** Numéro séquentiel annuel : FAC-2026-00001. */
    public static function genererNumero(): string
    {
        $annee = now()->format('Y');
        $longueur = (int) config('facture.numerotation.longueur', 5);

        $dernier = Commande::whereYear('created_at', $annee)
            ->where('numero_facture', 'like', "FAC-{$annee}-%")
            ->orderByDesc('numero_facture')
            ->value('numero_facture');

        $sequence = $dernier
            ? (int) substr((string) $dernier, -1 * $longueur) + 1
            : 1;

        // Garde-fou anti-collision (retry contrôlé par l'appelant)
        $numero = "FAC-{$annee}-".str_pad((string) $sequence, $longueur, '0', STR_PAD_LEFT);

        // L'index unique inclut les lignes supprimées (SoftDeletes) — vérifier avec withTrashed()
        if (Commande::withTrashed()->where('numero_facture', $numero)->exists()) {
            $numero = "FAC-{$annee}-".str_pad((string) random_int(1, (int) str_repeat('9', $longueur)), $longueur, '0', STR_PAD_LEFT);
        }

        return $numero;
    }

    /** Format international E.164 (ex: 690123456 → +237690123456). */
    public static function normaliserTelephone(string $brut): string
    {
        $prefixe = (string) config('facture.prefixe_defaut', '+237');
        $indicatif = ltrim($prefixe, '+'); // « 237 »

        $tel = preg_replace('/[^0-9+]/', '', trim($brut));

        if (str_starts_with($tel, '+')) {
            return $tel;
        }
        if (str_starts_with($tel, '00')) {
            return '+'.substr($tel, 2);
        }
        // Numéro déjà saisi avec l'indicatif pays sans le « + » (ex: 237690123456).
        if (str_starts_with($tel, $indicatif) && strlen($tel) >= 10) {
            return '+'.ltrim($tel, '0');
        }

        return $prefixe.$tel;
    }

    /** Téléphone compatible passerelle (meta/twilio : sans le « + »). */
    public static function telephonePasserelle(string $tel): string
    {
        return str_replace('+', '', self::normaliserTelephone($tel));
    }

    /** Dispatch du job WhatsApp (serveur → passerelle, jamais l'utilisateur). */
    public function envoyerWhatsApp(string $numeroFacture, string $phase): void
    {
        $facture = Facture::where('numero', $numeroFacture)->where('phase', $phase)->first();

        if (! $facture) {
            return;
        }

        WhatsappEnvoi::create([
            'facture_id' => $facture->id,
            'commande_id' => $facture->commande_id,
            'superviseur_id' => $this->superviseurId,
            'telephone' => $facture->client_whatsapp,
            'canal' => config('services.whatsapp.driver', 'log'),
            'statut' => WhatsappEnvoi::STATUT_EN_ATTENTE,
        ]);

        EnvoyerFactureWhatsAppJob::dispatch($facture->id);
    }
}
