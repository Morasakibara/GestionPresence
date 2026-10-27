<?php

namespace App\Http\Controllers;

use App\Jobs\EnvoyerFactureWhatsAppJob;
use App\Models\Commande;
use App\Models\Facture;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Superviseur;
use App\Models\WhatsappEnvoi;
use App\Services\AuditService;
use App\Services\CommandeSuperviseurService;
use App\Services\FacturePdfService;
use App\Services\FactureService;
use App\Services\ServiceFactureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur abstrait pour les postes spécialisés (directrice / secrétaire).
 *
 * Toute la logique CRUD commandes/services/retraits/rapport est centralisée ici.
 * Les contrôleurs concrets ne définissent que :
 *   - module()    → 'directrice' | 'secretaire'
 *   - types()     → le tableau des types autorisés
 *   - viewPrefix() → 'directrice' | 'secretaire' (préfixe des vues Blade)
 */
abstract class AbstractCaisseController extends Controller
{
    // ─── Méthodes abstraites à implémenter ─────────────────────────

    /** Nom du module pour les logs, routes et cache. */
    abstract protected function module(): string;

    /** Tableau des types autorisés ['clé' => 'Label']. */
    abstract protected function types(): array;

    /** Préfixe des vues Blade ('directrice' ou 'secretaire'). */
    abstract protected function viewPrefix(): string;

    // ─── Helpers ───────────────────────────────────────────────────

    protected function service(): CommandeSuperviseurService
    {
        return new CommandeSuperviseurService(Auth::id(), $this->module(), $this->types());
    }

    protected function cachePrefix(): string
    {
        return $this->module() . '_';
    }

    protected function flushDashboardCache(): void
    {
        $id = Auth::id();
        $prefix = $this->cachePrefix();
        \Illuminate\Support\Facades\Cache::forget("{$prefix}dash_{$id}");
        \Illuminate\Support\Facades\Cache::forget("{$prefix}types_{$id}_" . now()->toDateString());
    }

    protected function serviceFacture(): FactureService
    {
        return new FactureService(Auth::id(), 'facture_' . $this->module());
    }

    /** Service de facturation des services fournis (Étape 2 services). */
    protected function serviceFacturation(): ServiceFactureService
    {
        return new ServiceFactureService(Auth::id(), 'facture_' . $this->module());
    }

    protected function caisseType(): string
    {
        return $this->module();
    }

    /** Clé pour passer les types à la vue (typesCaisse ou typesPhoto). */
    protected function typesViewKey(): string
    {
        return 'typesCaisse';
    }

    // ─── DASHBOARD ─────────────────────────────────────────────────

    public function dashboard()
    {
        $superviseur = Auth::user();
        $prefix = $this->cachePrefix();
        $cacheKey = "{$prefix}dash_{$superviseur->id}";

        $superviseurInfo = \Illuminate\Support\Facades\Cache::remember(
            "superviseur_info_{$superviseur->id}",
            600,
            fn() => Superviseur::where('id', $superviseur->id)->first()
        );

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, fn() => $this->service()->dashboardData());

        // Les commandes par type viennent déjà du dashboardData caché (60 s)
        $today = now()->toDateString();
        $data['servicesParType'] = \Illuminate\Support\Facades\Cache::remember(
            "{$prefix}services_types_{$superviseur->id}_{$today}",
            60,
            fn() => ServiceFourni::where('superviseur_id', $superviseur->id)
                ->whereDate('date', $today)
                ->selectRaw('type, SUM(montant) as total')
                ->groupBy('type')->pluck('total', 'type')
        );

        return view("{$this->viewPrefix()}.dashboard", array_merge($data, [
            'superviseurInfo' => $superviseurInfo,
            $this->typesViewKey() => $this->types(),
        ]));
    }

    // ─── COMMANDES ─────────────────────────────────────────────────

    public function showCommandes()
    {
        $commandes = $this->service()->toutesLesCommandes();

        // Étape 4.2 — alertes visibles des envois WhatsApp en échec
        $envoisEchecs = WhatsappEnvoi::where('superviseur_id', Auth::id())
            ->where('statut', WhatsappEnvoi::STATUT_ECHEC)
            ->with('commande')
            ->latest()->take(10)->get();

        return view("{$this->viewPrefix()}.commandes", compact('commandes', 'envoisEchecs'))
            ->with($this->typesViewKey(), $this->types());
    }

    /**
     * Étape 2.1 — Nouveau flux : « Valider l'acompte ».
     * Si le formulaire du nouveau flux est utilisé (numero_whatsapp + acompte),
     * la commande passe par le service de facturation (PDF + WhatsApp automatiques).
     * Sinon, on garde l'ancien chemin (API mobile, scripts).
     */
    public function storeCommande(Request $request)
    {
        // Nouveau formulaire (acompte/WhatsApp) → service de facturation qui décide
        // s'il y a lieu de générer une facture (montant > 1 000 FCFA).
        if ($request->filled('numero_whatsapp') || $request->filled('acompte')) {
            $this->serviceFacture()->creerAcompte($request);
        } else {
            $this->service()->enregistrerCommande($request);
        }

        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', "Facture d'acompte enregistrée.");
    }

    public function editCommande($id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $commande);
        return view("{$this->viewPrefix()}.commandes_edit", compact('commande'))
            ->with($this->typesViewKey(), $this->types());
    }

    public function updateCommande(Request $request, $id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->first();
        if ($commande) $this->authorize('update', $commande);

        // Verrou (Étape 1.2) : une facture déjà encaissée ne bouge pas sans le mot de passe administrateur
        if ($commande && $commande->estVerrouillee()) {
            $autorisation = $this->serviceFacture()->autoriserMouvement($commande, 'modification', $request->input('manager_password'));
            if (!$autorisation['authorized']) {
                return redirect()->back()->with('error', 'Modification bloquée : cette facture a un acompte. Mot de passe administrateur requis.');
            }
        }

        $this->service()->modifierCommande($request, $id);
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande mise à jour.');
    }

    public function destroyCommande(Request $request, $id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->first();
        if ($commande) $this->authorize('delete', $commande);

        if ($commande) {
            $autorisation = $this->serviceFacture()->autoriserMouvement($commande, 'suppression', $request->input('manager_password'));
            if (!$autorisation['authorized']) {
                return redirect()->back()->with('error', 'Suppression bloquée : mot de passe administrateur requis pour une facture encaissée.');
            }
        }

        $this->service()->supprimerCommande($id);
        $this->flushDashboardCache();

        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande supprimée.');
    }

    /**
     * Mode minimal — page de liste sans les types (si le template n'a pas la variable).
     */
    public function listCommandes(Request $request)
    {
        $commandes = $this->service()->toutesLesCommandes();

        // Étape 4.2 — alertes visibles des envois WhatsApp en échec
        $envoisEchecs = WhatsappEnvoi::where('superviseur_id', Auth::id())
            ->where('statut', WhatsappEnvoi::STATUT_ECHEC)
            ->with('commande')
            ->latest()->take(10)->get();

        $data = ['commandes' => $commandes, 'envoisEchecs' => $envoisEchecs];
        $data[$this->typesViewKey()] = $this->types();

        return view("{$this->viewPrefix()}.commandes", $data);
    }

    public function annulerCommande(Request $request, $id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $commande);

        $autorisation = $this->serviceFacture()->autoriserMouvement($commande, 'annulation', $request->input('manager_password'));
        if (!$autorisation['authorized']) {
            return redirect()->back()->with('error', 'Annulation bloquée : mot de passe administrateur requis pour une facture encaissée.');
        }

        $this->service()->annulerCommande($id);
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande annulée.');
    }

    /** ───────── Étape 2.2 — Clôture du solde (montant verrouillé) ──────── */

    public function encaisserSolde(Request $request, $id)
    {
        $resultat = $this->serviceFacture()->encaisserSolde((int) $id, $request);
        $this->flushDashboardCache();

        if (!$resultat['success']) {
            return redirect()->route("{$this->module()}.commandes")->with('error', $resultat['error']);
        }

        return redirect()->route("{$this->module()}.commandes")->with('success', $resultat['message']);
    }

    /** ───────── Étape 3.1 — Facture PDF en lecture seule ───────────── */

    public function facturePdf(int $factureId)
    {
        $facture = Facture::whereKey($factureId)
            ->where(function ($q) {
                $q->whereHas('commande', fn($c) => $c->where('superviseur_id', Auth::id()))
                  ->orWhereHas('service', fn($s) => $s->where('superviseur_id', Auth::id()));
            })
            ->firstOrFail();

        if (!Storage::disk('local')->exists($facture->pdf_path)) {
            abort(404, 'PDF introuvable.');
        }

        if (!FacturePdfService::integrity($facture)) {
            abort(412, 'Facture altérée — contrôle d’empreinte SHA-256 en échec.');
        }

        return response()->download(
            Storage::disk('local')->path($facture->pdf_path),
            basename($facture->pdf_path),
            ['Content-Type' => 'application/pdf']
        )
        ->header('Cache-Control', 'no-store, private')
        ->header('X-Content-Type-Options', 'nosniff');
    }

    /** ───────── Étape 4.2 — Alertes d'envoi WhatsApp ───────────────── */

    public function renvoyerWhatsApp(Request $request, int $envoiId)
    {
        $envoi = WhatsappEnvoi::where('id', $envoiId)
            ->where('superviseur_id', Auth::id())
            ->with('facture')
            ->firstOrFail();

        // +237 par défaut : normaliser avant validation (oubli d'indicatif pays).
        if ($request->filled('numero_whatsapp')) {
            $request->merge([
                'numero_whatsapp' => FactureService::normaliserTelephone((string) $request->input('numero_whatsapp')),
            ]);
        }

        $request->validate(['numero_whatsapp' => ['required', 'string', 'regex:' . config('facture.whatsapp_regex')]]);

        $envoi->update([
            'telephone' => FactureService::normaliserTelephone($request->numero_whatsapp),
            'statut'    => WhatsappEnvoi::STATUT_EN_ATTENTE,
            'erreur'    => null,
            'tentative' => $envoi->tentative + 1,
        ]);

        // L'envoi peut concerner une commande OU un service (Étape 2 services)
        if ($envoi->commande) {
            $envoi->commande->update(['numero_whatsapp' => $envoi->telephone]);
        } elseif ($envoi->service) {
            $envoi->service->update(['numero_whatsapp' => $envoi->telephone]);
        }

        if ($envoi->facture) {
            EnvoyerFactureWhatsAppJob::dispatch($envoi->facture->id);
        }

        $this->flushDashboardCache();
        $page = $envoi->service ? 'services' : 'commandes';
        return redirect()->route("{$this->module()}.{$page}")->with('success', 'Nouvel envoi WhatsApp programmé sur ' . $envoi->telephone . '.');
    }

    // ─── ENCAISSEMENT ──────────────────────────────────────────────

    public function encaisserReste(Request $request)
    {
        $result = $this->service()->encaisserReste($request);
        $this->flushDashboardCache();

        if (!$result['success']) {
            return redirect()->route("{$this->module()}.dashboard")->with('error', $result['error']);
        }
        return redirect()->route("{$this->module()}.dashboard")->with('success', $result['message']);
    }

    // ─── SERVICES ──────────────────────────────────────────────────

    public function showServices()
    {
        $services = ServiceFourni::where('superviseur_id', Auth::id())->orderByDesc('created_at')->get();
        return view("{$this->viewPrefix()}.services", compact('services'))
            ->with($this->typesViewKey(), $this->types());
    }

    /**
     * Étape 2.1 services — un service n'accepte JAMAIS d'acompte : il est payé en
     * totalité dès la saisie. On route donc systématiquement par ServiceFactureService
     * (génère facture PDF + WhatsApp si le montant dépasse le seuil, sinon simple vente
     * réglée). L'ancien chemin « acompte partiel » n'existe plus pour les services.
     */
    public function storeService(Request $request)
    {
        $request->validate([
            'type'    => 'required|string|in:' . implode(',', array_keys($this->types())),
            'montant' => 'required|numeric|min:0.01|max:100000000',
            'details' => 'nullable|string|max:1000',
        ]);

        $this->serviceFacturation()->creerAcompte($request);

        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.services")->with('success', 'Service enregistré et payé intégralement.');
    }

    /** ───────── Étape 2.2 services — Clôture du solde (montant verrouillé) ──────── */

    public function encaisserSoldeService(Request $request, $id)
    {
        $resultat = $this->serviceFacturation()->encaisserSolde((int) $id, $request);
        $this->flushDashboardCache();

        if (!$resultat['success']) {
            return redirect()->route("{$this->module()}.services")->with('error', $resultat['error']);
        }

        return redirect()->route("{$this->module()}.services")->with('success', $resultat['message']);
    }

    public function editService($id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $service);
        return view("{$this->viewPrefix()}.services_edit", compact('service'))
            ->with($this->typesViewKey(), $this->types());
    }

    public function updateService(Request $request, $id)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01|max:100000000',
            'details' => 'nullable|string|max:1000',
        ]);

        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $service);

        // Verrou (Étape 1.2) : un service déjà encaissé ne bouge pas sans le mot de passe administrateur
        if ($service->estVerrouillee()) {
            $autorisation = $this->serviceFacturation()->autoriserMouvement($service, 'modification', $request->input('manager_password'));
            if (!$autorisation['authorized']) {
                return redirect()->back()->with('error', 'Modification bloquée : cette facture a un acompte. Mot de passe administrateur requis.');
            }
        }

        $service->update($request->only('montant', 'details'));
        $this->flushDashboardCache();

        return redirect()->route("{$this->module()}.services")->with('success', 'Service mis à jour.');
    }

    public function destroyService(Request $request, $id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('delete', $service);

        if ($service->estVerrouillee()) {
            $autorisation = $this->serviceFacturation()->autoriserMouvement($service, 'suppression', $request->input('manager_password'));
            if (!$autorisation['authorized']) {
                return redirect()->back()->with('error', 'Suppression bloquée : mot de passe administrateur requis pour une facture encaissée.');
            }
        }

        $service->delete();
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.services")->with('success', 'Service supprimé.');
    }

    // ─── RETRAITS ──────────────────────────────────────────────────

    public function showRetraits()
    {
        $retraits = Retrait::where('superviseur_id', Auth::id())->orderByDesc('created_at')->get();
        $caisse = new \App\Services\CaisseService(Auth::id(), $this->caisseType());
        $soldeArr = $caisse->solde();
        $solde = $soldeArr['en_caisse'];
        $totalEntrees = $soldeArr['total_commandes_paye'] + $soldeArr['total_services'];
        $totalSorties = $soldeArr['total_retraits'];
        $retraitStats = $caisse->retraitStats();

        return view("{$this->viewPrefix()}.retraits", array_merge(compact('retraits', 'solde', 'totalEntrees', 'totalSorties'), $retraitStats));
    }

    public function storeRetrait(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01|max:100000000',
            'motif'   => 'required|string|max:500',
        ]);

        $caisse = new \App\Services\CaisseService(Auth::id(), $this->caisseType());
        $solde = $caisse->solde()['en_caisse'];

        if ($request->montant > $solde) {
            return redirect()->route("{$this->module()}.retraits")
                ->with('error', 'Le retrait (' . number_format($request->montant, 0, ',', '.') . ' FCFA) dépasse le solde disponible (' . number_format($solde, 0, ',', '.') . ' FCFA).');
        }

        $rt = Retrait::create([
            'superviseur_id' => Auth::id(),
            'montant'        => $request->montant,
            'motif'          => $request->motif,
            'date'           => now()->toDateString(),
        ]);

        AuditService::created($this->module(), "Retrait de {$request->montant} FCFA — {$request->motif}", $rt->toArray(), $request);
        $this->flushDashboardCache();

        return redirect()->route("{$this->module()}.retraits")->with('success', 'Retrait enregistré.');
    }

    // ─── RAPPORT ───────────────────────────────────────────────────

    public function rapport(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $filters = [
            'type'        => $request->input('type', ''),
            'statut'      => $request->input('statut', ''),
            'montant_min' => $request->input('montant_min', ''),
            'montant_max' => $request->input('montant_max', ''),
        ];

        $data = $this->service()->rapportData($dateDebut, $dateFin, $filters);
        $data['dateDebut'] = $dateDebut;
        $data['dateFin'] = $dateFin;
        $data['filters'] = $filters;
        $data[$this->typesViewKey()] = $this->types();

        return view("{$this->viewPrefix()}.rapport", $data);
    }

    public function exportCsv(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $data = $this->service()->rapportCsvData($dateDebut, $dateFin);

        return \App\Services\ExportService::rapport(
            $dateDebut, $dateFin,
            $data['commandes'], $data['services'], $data['retraits'],
            $this->types(), $this->module()
        );
    }

    /**
     * Export HTML avec logo Le Pharaon — ouvrable dans le navigateur.
     */
    public function exportHtml(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $data = $this->service()->rapportCsvData($dateDebut, $dateFin);

        return \App\Services\ExportService::rapportHtml(
            $dateDebut, $dateFin,
            $data['commandes'], $data['services'], $data['retraits'],
            $this->types(), $this->module()
        );
    }

    /**
     * Export PDF avec logo Le Pharaon — téléchargeable.
     */
    public function exportPdf(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $data = $this->service()->rapportCsvData($dateDebut, $dateFin);

        $pdf = \App\Services\RapportCaissePdfService::generate(
            $dateDebut, $dateFin,
            $data['commandes'], $data['services'], $data['retraits'],
            $this->types(), $this->module()
        );

        $filename = 'rapport_' . $this->module() . '_' . $dateDebut . '_' . $dateFin . '.pdf';
        return $pdf->download($filename);
    }
}
