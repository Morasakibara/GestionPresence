<?php

namespace App\Http\Controllers;

use App\Models\StockTshirt;
use App\Models\StockPapier;
use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionnaireStockController extends Controller
{
    /**
     * Dashboard du gestionnaire de stock — vue d'ensemble des stocks.
     */
    public function dashboard()
    {
        $superviseur = Auth::user();

        $tshirts = StockTshirt::where('superviseur_id', $superviseur->id)->get();
        $papiers = StockPapier::where('superviseur_id', $superviseur->id)->get();

        $totalTshirts = $tshirts->sum('quantite');
        $tshirtsEnAlerte = $tshirts->filter(fn($t) => $t->enAlerte());
        $papiersEnAlerte = $papiers->filter(fn($p) => $p->enAlerte());

        return view('gestionnaire_stock.dashboard', compact(
            'tshirts', 'papiers', 'totalTshirts',
            'tshirtsEnAlerte', 'papiersEnAlerte'
        ));
    }

    // ===================== T-SHIRTS =====================

    public function showTshirts()
    {
        $superviseur = Auth::user();
        $tshirts = StockTshirt::where('superviseur_id', $superviseur->id)
            ->orderBy('couleur')->orderBy('taille')->get();

        return view('gestionnaire_stock.tshirts', compact('tshirts'));
    }

    public function storeTshirt(Request $request)
    {
        $request->validate([
            'couleur' => 'required|string|max:100',
            'taille' => 'required|string|max:20',
            'quantite' => 'required|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
        ]);

        // Upsert : si même couleur+taille existe déjà, mettre à jour
        $existing = StockTshirt::where('superviseur_id', Auth::id())
            ->where('couleur', $request->couleur)
            ->where('taille', $request->taille)
            ->first();

        if ($existing) {
            $existing->update([
                'quantite' => $request->quantite,
                'seuil_alerte' => $request->seuil_alerte ?? $existing->seuil_alerte,
            ]);
            return redirect()->route('gestionnaire.tshirts')->with('success', 'Stock mis à jour.');
        }

        StockTshirt::create([
            'superviseur_id' => Auth::id(),
            'couleur' => $request->couleur,
            'taille' => $request->taille,
            'quantite' => $request->quantite,
            'seuil_alerte' => $request->seuil_alerte ?? 5,
        ]);

        return redirect()->route('gestionnaire.tshirts')->with('success', 'T-shirt ajouté au stock.');
    }

    public function updateTshirt(Request $request, $id)
    {
        $request->validate([
            'quantite' => 'required|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
        ]);

        $tshirt = StockTshirt::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();

        $tshirt->update([
            'quantite' => $request->quantite,
            'seuil_alerte' => $request->seuil_alerte ?? $tshirt->seuil_alerte,
        ]);

        return redirect()->route('gestionnaire.tshirts')->with('success', 'Stock mis à jour.');
    }

    public function destroyTshirt($id)
    {
        $tshirt = StockTshirt::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();
        $tshirt->delete();

        return redirect()->route('gestionnaire.tshirts')->with('success', 'Entrée supprimée.');
    }

    // ===================== PAPIER =====================

    public function showPapier()
    {
        $superviseur = Auth::user();
        $papiers = StockPapier::where('superviseur_id', $superviseur->id)
            ->orderBy('imprimante')->get();

        return view('gestionnaire_stock.papier', compact('papiers'));
    }

    public function storePapier(Request $request)
    {
        $request->validate([
            'imprimante' => 'required|string|max:255',
            'metres_restants' => 'required|numeric|min:0',
            'metres_total' => 'required|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
        ]);

        $existing = StockPapier::where('superviseur_id', Auth::id())
            ->where('imprimante', $request->imprimante)
            ->first();

        if ($existing) {
            $existing->update([
                'metres_restants' => $request->metres_restants,
                'metres_total' => $request->metres_total,
                'seuil_alerte' => $request->seuil_alerte ?? $existing->seuil_alerte,
            ]);
            return redirect()->route('gestionnaire.papier')->with('success', 'Stock papier mis à jour.');
        }

        StockPapier::create([
            'superviseur_id' => Auth::id(),
            'imprimante' => $request->imprimante,
            'metres_restants' => $request->metres_restants,
            'metres_total' => $request->metres_total,
            'seuil_alerte' => $request->seuil_alerte ?? 50,
        ]);

        return redirect()->route('gestionnaire.papier')->with('success', 'Stock papier ajouté.');
    }

    public function updatePapier(Request $request, $id)
    {
        $request->validate([
            'metres_restants' => 'required|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
        ]);

        $papier = StockPapier::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();

        $papier->update([
            'metres_restants' => $request->metres_restants,
            'seuil_alerte' => $request->seuil_alerte ?? $papier->seuil_alerte,
        ]);

        return redirect()->route('gestionnaire.papier')->with('success', 'Stock papier mis à jour.');
    }

    public function destroyPapier($id)
    {
        $papier = StockPapier::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();
        $papier->delete();

        return redirect()->route('gestionnaire.papier')->with('success', 'Entrée supprimée.');
    }
}
