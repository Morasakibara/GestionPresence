<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SecretaireController extends Controller
{
    /**
     * Types de commandes/services autorisés pour la secrétaire.
     */
    private const TYPES_SECRETAIRE = [
        'shooting' => 'Shooting',
        'montage_photos' => 'Montage photos',
        'montage_agrandissement' => 'Montage & agrandissement photos',
        'agrandissement_photos' => 'Agrandissement photos',
        'demi_carte_photo' => 'Demi-carte photo',
    ];

    /**
     * Dashboard de la secrétaire — vue d'ensemble de la caisse photo.
     */
    public function dashboard()
    {
        $superviseur = Auth::user();
        $today = now()->toDateString();

        $commandesJour = Commande::where('superviseur_id', $superviseur->id)
            ->whereDate('date', $today)->get();
        $servicesJour = ServiceFourni::where('superviseur_id', $superviseur->id)
            ->whereDate('date', $today)->get();
        $retraitsJour = Retrait::where('superviseur_id', $superviseur->id)
            ->whereDate('date', $today)->get();

        $totalCommandes = $commandesJour->sum('montant');
        $totalServices = $servicesJour->sum('montant');
        $totalRetraits = $retraitsJour->sum('montant');
        $sommeEnCaisse = $totalCommandes + $totalServices - $totalRetraits;

        $commandesParType = $commandesJour->groupBy('type')->map(fn($c) => $c->sum('montant'));
        $servicesParType = $servicesJour->groupBy('type')->map(fn($s) => $s->sum('montant'));

        $derniersCommandes = $commandesJour->sortByDesc('created_at')->take(5);
        $derniersServices = $servicesJour->sortByDesc('created_at')->take(5);
        $derniersRetraits = $retraitsJour->sortByDesc('created_at')->take(5);

        return view('secretaire.dashboard', compact(
            'totalCommandes', 'totalServices', 'totalRetraits',
            'sommeEnCaisse', 'commandesParType', 'servicesParType',
            'derniersCommandes', 'derniersServices', 'derniersRetraits'
        ))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    /**
     * Page des commandes photo.
     */
    public function showCommandes()
    {
        $superviseur = Auth::user();
        $commandes = Commande::where('superviseur_id', $superviseur->id)
            ->whereDate('date', now()->toDateString())
            ->orderByDesc('created_at')->get();

        return view('secretaire.commandes', compact('commandes'))
            ->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    /**
     * Enregistrer une commande photo.
     */
    public function storeCommande(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(self::TYPES_SECRETAIRE)),
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);

        Commande::create([
            'superviseur_id' => Auth::id(),
            'type' => $request->type,
            'montant' => $request->montant,
            'details' => $request->details,
            'date' => now()->toDateString(),
        ]);

        return redirect()->route('secretaire.commandes')->with('success', 'Commande enregistrée.');
    }

    public function editCommande($id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        return view('secretaire.commandes_edit', compact('commande'))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function updateCommande(Request $request, $id)
    {
        $commande = Commande::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(self::TYPES_SECRETAIRE)),
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);
        $commande->update($request->only('type', 'montant', 'details'));
        return redirect()->route('secretaire.commandes')->with('success', 'Commande mise à jour.');
    }

    public function destroyCommande($id)
    {
        $commande = Commande::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();
        $commande->delete();

        return redirect()->route('secretaire.commandes')->with('success', 'Commande supprimée.');
    }

    /**
     * Page des services photo.
     */
    public function showServices()
    {
        $superviseur = Auth::user();
        $services = ServiceFourni::where('superviseur_id', $superviseur->id)
            ->whereDate('date', now()->toDateString())
            ->orderByDesc('created_at')->get();

        return view('secretaire.services', compact('services'))
            ->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(self::TYPES_SECRETAIRE)),
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);

        ServiceFourni::create([
            'superviseur_id' => Auth::id(),
            'type' => $request->type,
            'montant' => $request->montant,
            'details' => $request->details,
            'date' => now()->toDateString(),
        ]);

        return redirect()->route('secretaire.services')->with('success', 'Service enregistré.');
    }

    public function editService($id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        return view('secretaire.services_edit', compact('service'))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function updateService(Request $request, $id)
    {
        $service = ServiceFourni::where('id', $id)->where('superviseur_id', Auth::id())->firstOrFail();
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(self::TYPES_SECRETAIRE)),
            'montant' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:1000',
        ]);
        $service->update($request->only('type', 'montant', 'details'));
        return redirect()->route('secretaire.services')->with('success', 'Service mis à jour.');
    }

    public function destroyService($id)
    {
        $service = ServiceFourni::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();
        $service->delete();

        return redirect()->route('secretaire.services')->with('success', 'Service supprimé.');
    }

    /**
     * Page des retraits.
     */
    public function showRetraits()
    {
        $superviseur = Auth::user();
        $retraits = Retrait::where('superviseur_id', $superviseur->id)
            ->whereDate('date', now()->toDateString())
            ->orderByDesc('created_at')->get();

        $totalEntrees = Commande::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant')
            + ServiceFourni::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant');
        $totalSorties = Retrait::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant');

        return view('secretaire.retraits', compact('retraits', 'totalEntrees', 'totalSorties'));
    }

    public function storeRetrait(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'motif' => 'required|string|max:1000',
        ]);

        $totalEntrees = Commande::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant')
            + ServiceFourni::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant');
        $totalSorties = Retrait::where('superviseur_id', Auth::id())
            ->whereDate('date', now()->toDateString())->sum('montant');
        $disponible = $totalEntrees - $totalSorties;

        if ($request->montant > $disponible) {
            return redirect()->route('secretaire.retraits')
                ->with('error', 'Montant supérieur à la somme disponible (' . number_format($disponible, 2, ',', '') . ' FCFA).');
        }

        Retrait::create([
            'superviseur_id' => Auth::id(),
            'montant' => $request->montant,
            'motif' => $request->motif,
            'date' => now()->toDateString(),
        ]);

        return redirect()->route('secretaire.retraits')->with('success', 'Retrait enregistré.');
    }

    /**
     * Rapport pour la secrétaire.
     */
    public function rapport(Request $request)
    {
        $superviseur = Auth::user();
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->input('date_fin', now()->toDateString());

        $query = function ($type, $debut, $fin) use ($superviseur) {
            return $type::where('superviseur_id', $superviseur->id)
                ->whereBetween('date', [$debut, $fin]);
        };

        $totalCommandes = $query(Commande::class, $dateDebut, $dateFin)->sum('montant');
        $totalServices = $query(ServiceFourni::class, $dateDebut, $dateFin)->sum('montant');
        $totalRetraits = $query(Retrait::class, $dateDebut, $dateFin)->sum('montant');

        $commandesParType = $query(Commande::class, $dateDebut, $dateFin)
            ->get()->groupBy('type')->map(fn($c) => $c->sum('montant'));
        $servicesParType = $query(ServiceFourni::class, $dateDebut, $dateFin)
            ->get()->groupBy('type')->map(fn($s) => $s->sum('montant'));

        $detailJournalier = collect();
        $debut = Carbon::parse($dateDebut);
        $fin = Carbon::parse($dateFin);
        while ($debut->lte($fin)) {
            $jour = $debut->toDateString();
            $entrees = Commande::where('superviseur_id', $superviseur->id)->whereDate('date', $jour)->sum('montant')
                + ServiceFourni::where('superviseur_id', $superviseur->id)->whereDate('date', $jour)->sum('montant');
            $sorties = Retrait::where('superviseur_id', $superviseur->id)->whereDate('date', $jour)->sum('montant');
            $detailJournalier->push([
                'date' => $jour,
                'label' => $debut->format('d/m'),
                'entrees' => $entrees,
                'sorties' => $sorties,
            ]);
            $debut->addDay();
        }

        return view('secretaire.rapport', compact(
            'dateDebut', 'dateFin',
            'totalCommandes', 'totalServices', 'totalRetraits',
            'commandesParType', 'servicesParType', 'detailJournalier'
        ))->with('typesPhoto', self::TYPES_SECRETAIRE);
    }

    public function exportCsv(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->input('date_fin', now()->toDateString());
        $superviseur = Auth::user();

        $lignes = ["Type;Montant;Détails;Date"];

        $commandes = Commande::where('superviseur_id', $superviseur->id)
            ->whereBetween('date', [$dateDebut, $dateFin])->get();
        foreach ($commandes as $c) {
            $lignes[] = 'Commande - ' . (self::TYPES_SECRETAIRE[$c->type] ?? $c->type) . ';' . $c->montant . ';' . ($c->details ?? '') . ';' . $c->date->format('d/m/Y');
        }

        $services = ServiceFourni::where('superviseur_id', $superviseur->id)
            ->whereBetween('date', [$dateDebut, $dateFin])->get();
        foreach ($services as $s) {
            $lignes[] = 'Service - ' . (self::TYPES_SECRETAIRE[$s->type] ?? $s->type) . ';' . $s->montant . ';' . ($s->details ?? '') . ';' . $s->date->format('d/m/Y');
        }

        $retraits = Retrait::where('superviseur_id', $superviseur->id)
            ->whereBetween('date', [$dateDebut, $dateFin])->get();
        foreach ($retraits as $r) {
            $lignes[] = 'Retrait;' . $r->montant . ';' . $r->motif . ';' . $r->date->format('d/m/Y');
        }

        $contenu = implode("\n", $lignes) . "\n";

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=rapport_secretaire_' . $dateDebut . '_' . $dateFin . '.csv',
        ]);
    }
}
