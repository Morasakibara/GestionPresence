<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCongeController extends Controller
{
    /**
     * Vue admin consolidée : tous les congés de l'entreprise.
     */
    public function index(Request $request)
    {
        $query = Conge::with('employe', 'traitePar')
            ->orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('equipe')) {
            $equipeSup = Superviseur::where('equipe', $request->equipe)->pluck('id');
            $equipeEmployeIds = DB::table('employer')->whereIn('Sup_id', $equipeSup)->pluck('id');
            $query->whereIn('employer_id', $equipeEmployeIds);
        }

        $conges = $query->paginate(20)->withQueryString();

        // Stats globales
        $stats = [
            'total' => Conge::count(),
            'en_attente' => Conge::where('statut', 'en_attente')->count(),
            'approuves' => Conge::where('statut', 'approuve')->count(),
            'refuses' => Conge::where('statut', 'refuse')->count(),
        ];

        // Équipes disponibles
        $equipes = Superviseur::whereNotNull('equipe')->where('equipe', '!=', '')->distinct()->pluck('equipe')->sort()->values();

        // Données pour graphique
        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $chartLabels[] = ucfirst($mois->locale('fr')->isoFormat('MMM'));
            $chartData[] = Conge::whereMonth('date_debut', $mois->month)
                ->whereYear('date_debut', $mois->year)
                ->count();
        }

        return view('admin.conges', compact('conges', 'stats', 'equipes', 'chartLabels', 'chartData'));
    }
}
