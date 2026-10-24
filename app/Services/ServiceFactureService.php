<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\ServiceFourni;
use App\Models\Utilisateur;
use App\Models\WhatsappEnvoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Étapes 2.1 / 2.2 — Facturation des services fournis.
 *
 * Même orchestre que FactureService (commandes), appliqué à ServiceFourni :
 * acompte initial → PDF figé (hash SHA-256) → WhatsApp automatique, puis
 * clôture par encaissement du solde exact (aucune saisie manuelle).
 */
class ServiceFactureService
{
    public function __construct(
        private readonly int $superviseurId,
        private readonly string $module,
        private readonly ManagerAuthService $managerAuth = new ManagerAuthService(),
    ) {}

    /*────────────── Étape 2.1 — Saisie initiale (acompte) ──────────────*/

    /**
     * Crée le service + facture d'acompte via le formulaire de facturation.
     *
     * Champs identiques au flux commandes : montant (total), acompte,
     * numero_whatsapp (obligatoire, format +237...), client_nom, details.
     */
    public function creerAcompte(Request $request): ServiceFourni
    {
        $request->validate([
            'montant'         => 'required|numeric|min:1|max:100000000',
            'acompte'         => 'required|numeric|min:1|max:100000000',
            'client_nom'      => 'nullable|string|max:100',
            'numero_whatsapp' => ['required', 'string', 'regex:' . config('facture.whatsapp_regex')],
            'details'         => 'nullable|string|max:1000',
        ], [
            'acompte.required' => 'L\'acompte est obligatoire.',
            'acompte.min'      => 'L\'acompte doit être supérieur à 0.',
            'numero_whatsapp.required' => 'Le numéro WhatsApp du client est obligatoire.',
        ]);

        $montant  = round((float) $request->montant, 2);
        $acompte  = round((float) $request->acompte, 2);

        if ($acompte > $montant) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'acompte' => "L'acompte (" . number_format($acompte, 0, ',', '.') .
                    ") ne peut pas dépasser le montant total (" . number_format($montant, 0, ',', '.') . ").",
            ]);
        }

        $comptant = $acompte >= $montant;

        $service = DB::transaction(function () use ($request, $montant, $acompte, $comptant) {
            $numero = self::genererNumeroService();

            $service = ServiceFourni::create([
                'superviseur_id'  => $this->superviseurId,
                'type'            => $request->type,
                'montant'         => $montant,
                'montant_paye'    => $acompte,
                'statut_paiement' => $comptant ? 'paye' : ServiceFourni::STATUT_EN_ATTENTE_SOLDE,
                'details'         => $request->details,
                'date'            => now()->toDateString(),
                'numero_whatsapp' => FactureService::normaliserTelephone($request->numero_whatsapp),
                'client_nom'      => $request->client_nom,
                'numero_facture'  => $numero,
                'prix_verrouille' => true, // verrou dès le premier versement
            ]);

            FacturePdfService::figerService(
                $service,
                $numero,
                $comptant ? Facture::PHASE_SOLDE : Facture::PHASE_ACOMPTE
            );

            return $service;
        });

        AuditService::created(
            $this->module,
            "Facture {$service->numero_facture} — acompte " . number_format($acompte, 0, ',', '.') .
            " FCFA sur " . number_format($montant, 0, ',', '.') . " FCFA (WhatsApp {$service->numero_whatsapp})",
            $service->toArray(),
            $request
        );

        // Étape 4.2 — envoi WhatsApp automatique dès la confirmation du paiement
        $this->envoyerWhatsApp($service->numero_facture, Facture::PHASE_ACOMPTE);

        return $service;
    }

    /*────────────── Étape 2.2 — Clôture (solde) ──────────────*/

    /**
     * Encaisse le solde exact d'un service en attente — même verrou que les
     * commandes : le serveur calcule le reste et bascule à « Payée ».
     */
    public function encaisserSolde(int $serviceId, ?Request $request = null): array
    {
        $service = ServiceFourni::where('id', $serviceId)
            ->where('superviseur_id', $this->superviseurId)
            ->firstOrFail();

        if ($service->statut_paiement === 'paye') {
            return ['success' => false, 'error' => "La facture {$service->numero_facture} est déjà payée."];
        }
        if (in_array($service->statut_paiement, ['annule'], true)) {
            return ['success' => false, 'error' => "Service annulé — encaissement impossible."];
        }

        $solde = round($service->solde, 2);

        $ancien = $service->toArray();
        $service->update([
            'montant_paye'    => round((float) $service->montant, 2),
            'statut_paiement' => 'paye',
        ]);

        FacturePdfService::figerService($service, $service->numero_facture, Facture::PHASE_SOLDE);

        AuditService::updated(
            $this->module,
            "Encaissement du solde — facture {$service->numero_facture} : {$solde} FCFA → PAYÉE",
            $ancien,
            $service->toArray(),
            $request
        );

        try {
            $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\PaiementServiceNotification($service, $solde, 'paye'));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification paiement échouée: ' . $e->getMessage());
        }

        $this->envoyerWhatsApp($service->numero_facture, Facture::PHASE_SOLDE);

        return [
            'success' => true,
            'message' => "Facture {$service->numero_facture} payée — solde " .
                number_format($solde, 0, ',', '.') . " FCFA encaissé.",
        ];
    }

    /*────────────── Verrou manager (Étape 1.2) ──────────────*/

    /**
     * Vérifie l'autorisation manager sur un service verrouillé.
     *
     * @return array{authorized: bool, gestionnaire: ?Utilisateur}
     */
    public function autoriserMouvement(ServiceFourni $service, string $action, ?string $motDePasseManager): array
    {
        if (!$service->estVerrouillee()) {
            return ['authorized' => true, 'gestionnaire' => null];
        }

        $gestionnaire = $this->managerAuth->valider($motDePasseManager);

        $this->managerAuth->tracer(
            "caisse.{$action}",
            "{$action} sur la facture verrouillée {$service->numero_facture}",
            $gestionnaire,
            \Illuminate\Support\Facades\Auth::user()
        );

        return ['authorized' => $gestionnaire !== null, 'gestionnaire' => $gestionnaire];
    }

    /*────────────── Aides ──────────────*/

    /** Numéro séquentiel annuel : SVC-2026-00001. */
    public static function genererNumeroService(): string
    {
        $prefixe = (string) config('facture.numerotation.prefixe_service', 'SVC-');
        $annee   = now()->format('Y');
        $longueur = (int) config('facture.numerotation.longueur', 5);

        $dernier = ServiceFourni::whereYear('created_at', $annee)
            ->where('numero_facture', 'like', "{$prefixe}{$annee}-%")
            ->orderByDesc('numero_facture')
            ->value('numero_facture');

        $sequence = $dernier
            ? (int) substr((string) $dernier, -1 * $longueur) + 1
            : 1;

        $numero = "{$prefixe}{$annee}-" . str_pad((string) $sequence, $longueur, '0', STR_PAD_LEFT);

        // L'index unique inclut les lignes supprimées (SoftDeletes) — vérifier avec withTrashed()
        if (ServiceFourni::withTrashed()->where('numero_facture', $numero)->exists()) {
            $numero = "{$prefixe}{$annee}-" . str_pad((string) random_int(1, (int) str_repeat('9', $longueur)), $longueur, '0', STR_PAD_LEFT);
        }

        return $numero;
    }

    /** Dispatch du job WhatsApp (serveur → passerelle, jamais l'utilisateur). */
    public function envoyerWhatsApp(string $numeroFacture, string $phase): void
    {
        $facture = Facture::where('numero', $numeroFacture)->where('phase', $phase)->first();

        if (!$facture) {
            return;
        }

        WhatsappEnvoi::create([
            'facture_id'     => $facture->id,
            'service_id'     => $facture->service_id,
            'superviseur_id' => $this->superviseurId,
            'telephone'      => $facture->client_whatsapp,
            'canal'          => config('services.whatsapp.driver', 'log'),
            'statut'         => WhatsappEnvoi::STATUT_EN_ATTENTE,
        ]);

        \App\Jobs\EnvoyerFactureWhatsAppJob::dispatch($facture->id);
    }
}
