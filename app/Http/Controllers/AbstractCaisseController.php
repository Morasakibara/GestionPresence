<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Superviseur;
use App\Services\AuditService;
use App\Services\CommandeSuperviseurService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $today = now()->toDateString();
        $todayKey = "{$prefix}types_{$superviseur->id}_{$today}";
        $types = \Illuminate\Support\Facades\Cache::remember($todayKey, 60, function () use ($superviseur, $today) {
            $commandesJour = Commande::where('superviseur_id', $superviseur->id)
                ->whereDate('date', $today)->get();
            return $commandesJour->groupBy('type')->map(fn($c) => $c->sum('montant'));
        });

        $data['commandesParType'] = $types;
        $data['servicesParType'] = ServiceFourni::where('superviseur_id', $superviseur->id)
            ->whereDate('date', $today)->get()
            ->groupBy('type')->map(fn($s) => $s->sum('montant'));

        return view("{$this->viewPrefix()}.dashboard", array_merge($data, [
            'superviseurInfo' => $superviseurInfo,
            $this->typesViewKey() => $this->types(),
        ]));
    }

    // ─── COMMANDES ─────────────────────────────────────────────────

    public function showCommandes()
    {
        $commandes = $this->service()->toutesLesCommandes();
        return view("{$this->viewPrefix()}.commandes", compact('commandes'))
            ->with($this->typesViewKey(), $this->types());
    }

    public function storeCommande(Request $request)
    {
        $this->service()->enregistrerCommande($request);
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande enregistrée.');
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
        $this->service()->modifierCommande($request, $id);
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande mise à jour.');
    }

    public function destroyCommande($id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->first();
        if ($commande) $this->authorize('delete', $commande);
        $this->service()->supprimerCommande($id);
        $this->flushDashboardCache();
        return redirect()->route("{$this->module()}.commandes")->with('success', 'Commande supprimée.');
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

    public function storeService(Request $request)
    {
        $request->validate([
            'type'    => 'required|string|in:' . implode(',', array_keys($this->types())),
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);

        $svc = ServiceFourni::create([
            'superviseur_id' => Auth::id(),
            'type'           => $request->type,
            'montant'        => $request->montant,
            'details'        => $request->details,
            'date'           => now()->toDateString(),
        ]);

        AuditService::created($this->module(), "Service {$request->type} de {$request->montant} FCFA", $svc->toArray(), $request);
        $this->flushDashboardCache();

        return redirect()->route("{$this->module()}.services")->with('success', 'Service enregistré.');
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
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);

        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $service);
        $service->update($request->only('montant', 'details'));
        $this->flushDashboardCache();

        return redirect()->route("{$this->module()}.services")->with('success', 'Service mis à jour.');
    }

    public function destroyService($id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('delete', $service);
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
            'montant' => 'required|numeric|min:0.01',
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
