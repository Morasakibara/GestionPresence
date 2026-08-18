<?php

namespace App\Http\Controllers;

use App\Models\StockTshirt;
use App\Models\StockPapier;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Notifications\StockAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    private function alerteSiSeuilFracchie(int $superviseurId, string $type, string $details, float $quantite, float $seuil): void
    {
        if ($quantite > $seuil) return;
        $adminPrincipal = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        if ($adminPrincipal) {
            $adminPrincipal->notify(new StockAlertNotification($type, $details, $quantite, $seuil));
        }
    }

    public function storeTshirt(Request $request)
    {
        $request->validate([
            'couleur' => 'required|string|max:100',
            'taille' => 'required|string|max:20',
            'quantite' => 'required|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $existing = StockTshirt::where('superviseur_id', Auth::id())
                ->where('couleur', $request->couleur)
                ->where('taille', $request->taille)
                ->first();

            $seuil = $request->seuil_alerte ?? ($existing ? $existing->seuil_alerte : 5);

            if ($existing) {
                $existing->update([
                    'quantite' => $request->quantite,
                    'seuil_alerte' => $seuil,
                ]);
            } else {
                StockTshirt::create([
                    'superviseur_id' => Auth::id(),
                    'couleur' => $request->couleur,
                    'taille' => $request->taille,
                    'quantite' => $request->quantite,
                    'seuil_alerte' => $seuil,
                ]);
            }

            $this->alerteSiSeuilFracchie(Auth::id(), 'tshirt', $request->couleur . ' ' . $request->taille, $request->quantite, $seuil);
        });

        return redirect()->route('gestionnaire.tshirts')->with('success', 'Stock T-shirt enregistré.');
    }

    public function updateTshirt(Request $request, int $id)
    {
        $request->validate([
            'quantite' => 'required|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
        ]);

        $tshirt = StockTshirt::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();

        $seuil = $request->seuil_alerte ?? $tshirt->seuil_alerte;
        $tshirt->update([
            'quantite' => $request->quantite,
            'seuil_alerte' => $seuil,
        ]);
        $this->alerteSiSeuilFracchie(Auth::id(), 'tshirt', $tshirt->couleur . ' ' . $tshirt->taille, $request->quantite, $seuil);

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

        $seuil = $request->seuil_alerte ?? ($existing ? $existing->seuil_alerte : 50);

        if ($existing) {
            $existing->update([
                'metres_restants' => $request->metres_restants,
                'metres_total' => $request->metres_total,
                'seuil_alerte' => $seuil,
            ]);
            $this->alerteSiSeuilFracchie(Auth::id(), 'papier', $request->imprimante, $request->metres_restants, $seuil);
            return redirect()->route('gestionnaire.papier')->with('success', 'Stock papier mis à jour.');
        }

        StockPapier::create([
            'superviseur_id' => Auth::id(),
            'imprimante' => $request->imprimante,
            'metres_restants' => $request->metres_restants,
            'metres_total' => $request->metres_total,
            'seuil_alerte' => $seuil,
        ]);
        $this->alerteSiSeuilFracchie(Auth::id(), 'papier', $request->imprimante, $request->metres_restants, $seuil);

        return redirect()->route('gestionnaire.papier')->with('success', 'Stock papier ajouté.');
    }

    public function updatePapier(Request $request, int $id)
    {
        $request->validate([
            'metres_restants' => 'required|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
        ]);

        $papier = StockPapier::where('id', $id)
            ->where('superviseur_id', Auth::id())->firstOrFail();

        $seuil = $request->seuil_alerte ?? $papier->seuil_alerte;
        $papier->update([
            'metres_restants' => $request->metres_restants,
            'seuil_alerte' => $seuil,
        ]);
        $this->alerteSiSeuilFracchie(Auth::id(), 'papier', $papier->imprimante, $request->metres_restants, $seuil);

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
