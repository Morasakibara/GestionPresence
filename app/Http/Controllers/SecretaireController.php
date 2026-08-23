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
use Carbon\Carbon;

class SecretaireController extends Controller
{
    private const TYPES_SECRETAIRE = [
        'shooting'                 => 'Shooting',
        'montage_photos'           => 'Montage photos',
        'montage_agrandissement'   => 'Montage & agrandissement photos',
        'agrandissement_photos'    => 'Agrandissement photos',
        'demi_carte_photo'         => 'Demi-carte photo',
    ];

    /**────────────────── SERVICE ──────────────────*/

    private function service(): CommandeSuperviseurService
    {
        return new CommandeSuperviseurService(Auth::id(), 'secretaire', self::TYPES_SECRETAIRE);
    }

    private function flushDashboardCache(): void
    {
        $id = Auth::id();
        \Illuminate\Support\Facades\Cache::forget("secretaire_dash_{$id}");
        \Illuminate\Support\Facades\Cache::forget("secretaire_types_{$id}_" . now()->toDateString());
    }

    /**────────────────── DASHBOARD ──────────────────*/

    public function dashboard()
    {
        $superviseur = Auth::user();
        $today = now()->toDateString();
        $cacheKey = "secretaire_dash_{$superviseur->id}";

        $caisse = new \App\Services\CaisseService($superviseur->id, 'secretaire');
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, fn() => $caisse->dashboardData());

        $todayKey = "secretaire_types_{$superviseur->id}_{$today}";
        $types = \Illuminate\Support\Facades\Cache::remember($todayKey, 60, function () use ($superviseur, $today) {
            $commandesJour = Commande::where('superviseur_id', $superviseur->id)->whereDate('date', $today)->get();
            return [
                'commandesParType' => $commandesJour->groupBy('type')->map(fn($c) => $c->sum('montant')),
                'servicesParType'  => $commandesJour->groupBy('type')->map(fn($c) => $c->sum('montant')),
            ];
        });
        $data['commandesParType'] = $types['commandesParType'];
        $data['servicesParType']  = $types['servicesParType'];

        return view('secretaire.dashboard', $data)->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    /**────────────────── COMMANDES ──────────────────*/

    public function showCommandes()
    {
        $commandes = $this->service()->toutesLesCommandes();
        return view('secretaire.commandes', compact('commandes'))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function storeCommande(Request $request)
    {
        $this->service()->enregistrerCommande($request);
        $this->flushDashboardCache();
        return redirect()->route('secretaire.commandes')->with('success', 'Commande enregistrée.');
    }

    public function editCommande($id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $commande);
        return view('secretaire.commandes_edit', compact('commande'))->with('typesCaisse', self::TYPES_SECRETAIRE);
    }

    public function updateCommande(Request $request, $id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->first();
        if ($commande) $this->authorize('update', $commande);
        $this->service()->modifierCommande($request, $id);
        $this->flushDashboardCache();
        return redirect()->route('secretaire.commandes')->with('success', 'Commande mise à jour.');
    }

    public function destroyCommande($id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->first();
        if ($commande) $this->authorize('delete', $commande);
        $this->service()->supprimerCommande($id);
        $this->flushDashboardCache();
        return redirect()->route('secretaire.commandes')->with('success', 'Commande supprimée.');
    }

    /**────────────────── ENCAISSEMENT ──────────────────*/

    public function encaisserReste(Request $request)
    {
        $result = $this->service()->encaisserReste($request);
        $this->flushDashboardCache();

        if (!$result['success']) {
            return redirect()->route('secretaire.dashboard')->with('error', $result['error']);
        }
        return redirect()->route('secretaire.dashboard')->with('success', $result['message']);
    }

    /**────────────────── SERVICES ──────────────────*/

    public function showServices()
    {
        $services = ServiceFourni::where('superviseur_id', Auth::id())->orderByDesc('created_at')->get();
        return view('secretaire.services', compact('services'))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'type'    => 'required|string|in:' . implode(',', array_keys(self::TYPES_SECRETAIRE)),
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

        AuditService::created('secretaire', "Service {$request->type} de {$request->montant} FCFA", $svc->toArray(), $request);
        $this->flushDashboardCache();

        return redirect()->route('secretaire.services')->with('success', 'Service enregistré.');
    }

    public function editService($id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('update', $service);
        return view('secretaire.services_edit', compact('service'))->with('typesPhoto', self::TYPES_SECRETAIRE);
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

        return redirect()->route('secretaire.services')->with('success', 'Service mis à jour.');
    }

    public function destroyService($id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $this->authorize('delete', $service);
        $service->delete();
        $this->flushDashboardCache();
        return redirect()->route('secretaire.services')->with('success', 'Service supprimé.');
    }

    /**────────────────── RETRAITS ──────────────────*/

    public function showRetraits()
    {
        $retraits = Retrait::where('superviseur_id', Auth::id())->orderByDesc('created_at')->get();
        $caisse = new \App\Services\CaisseService(Auth::id(), 'secretaire');
        $soldeArr = $caisse->solde();
        $solde = $soldeArr['en_caisse'];
        $totalEntrees = $soldeArr['total_commandes_paye'] + $soldeArr['total_services'];
        $totalSorties = $soldeArr['total_retraits'];
        return view('secretaire.retraits', compact('retraits', 'solde', 'totalEntrees', 'totalSorties'));
    }

    public function storeRetrait(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'motif'   => 'required|string|max:500',
        ]);

        $caisse = new \App\Services\CaisseService(Auth::id(), 'secretaire');
        $solde = $caisse->solde()['en_caisse'];

        if ($request->montant > $solde) {
            return redirect()->route('secretaire.retraits')
                ->with('error', 'Le retrait dépasse le solde disponible (' . number_format($solde, 0, ',', '.') . ' FCFA).');
        }

        $rt = Retrait::create([
            'superviseur_id' => Auth::id(),
            'montant'        => $request->montant,
            'motif'          => $request->motif,
            'date'           => now()->toDateString(),
        ]);

        AuditService::created('secretaire', "Retrait de {$request->montant} FCFA — {$request->motif}", $rt->toArray(), $request);
        $this->flushDashboardCache();

        return redirect()->route('secretaire.retraits')->with('success', 'Retrait enregistré.');
    }

    /**────────────────── RAPPORT ──────────────────*/

    public function rapport(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $data = $this->service()->rapportData($dateDebut, $dateFin);
        $data['dateDebut']  = $dateDebut;
        $data['dateFin']    = $dateFin;
        $data['typesCaisse'] = self::TYPES_SECRETAIRE;

        return view('secretaire.rapport', $data);
    }

    public function exportCsv(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', now()->toDateString());

        $data = $this->service()->rapportCsvData($dateDebut, $dateFin);

        return \App\Services\ExportService::rapport(
            $dateDebut, $dateFin,
            $data['commandes'], $data['services'], $data['retraits'],
            self::TYPES_SECRETAIRE, 'secretaire'
        );
    }
}
