<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\Utilisateur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CongePdfService
{
    /**
     * Génère un PDF du planning des congés approuvés pour un mois donné.
     */
    public static function planning(string $mois): \Barryvdh\DomPDF\PDF
    {
        $debut = Carbon::parse($mois . '-01');
        $fin = $debut->copy()->endOfMonth();

        $conges = Conge::with('employe')
            ->where('statut', 'approuve')
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin', [$debut, $fin])
                  ->orWhere(function ($q2) use ($debut, $fin) {
                      $q2->where('date_debut', '<=', $debut)
                         ->where('date_fin', '>=', $fin);
                  });
            })
            ->orderBy('date_debut')
            ->get();

        // Grouper par semaine
        $semaines = [];
        $current = $debut->copy()->startOfWeek(Carbon::MONDAY);
        while ($current->lte($fin)) {
            $semaineFin = $current->copy()->endOfWeek(Carbon::SUNDAY);
            $semaines[] = [
                'debut' => $current->copy(),
                'fin' => $semaineFin->copy(),
                'conges' => $conges->filter(function ($c) use ($current, $semaineFin) {
                    return $c->date_debut->lte($semaineFin) && $c->date_fin->gte($current);
                })->values(),
            ];
            $current->addWeek();
        }

        $moisLabel = ucfirst($debut->locale('fr')->isoFormat('MMMM YYYY'));
        $admin = \Illuminate\Support\Facades\Auth::user();

        return Pdf::loadView('pdf.conges_planning', compact('conges', 'semaines', 'mois', 'moisLabel', 'debut', 'fin', 'admin'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'defaultFont' => 'sans-serif',
                'margin' => [15, 15, 15, 15],
            ]);
    }

    /**
     * Génère un PDF du résumé global des congés.
     */
    public static function resume(): \Barryvdh\DomPDF\PDF
    {
        $stats = [
            'total' => Conge::count(),
            'en_attente' => Conge::where('statut', 'en_attente')->count(),
            'approuves' => Conge::where('statut', 'approuve')->count(),
            'refuses' => Conge::where('statut', 'refuse')->count(),
        ];

        // Top 10 employés avec le plus de congés
        $topConges = Conge::select('employer_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('employer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = Utilisateur::find($item->employer_id);
                return [
                    'nom' => $user->nom ?? 'N/A',
                    'total' => $item->total,
                    'approuves' => Conge::where('employer_id', $item->employer_id)->where('statut', 'approuve')->count(),
                ];
            });

        // Dernières demandes
        $recentes = Conge::with('employe')->latest()->limit(15)->get();

        $admin = \Illuminate\Support\Facades\Auth::user();

        return Pdf::loadView('pdf.conges_resume', compact('stats', 'topConges', 'recentes', 'admin'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'defaultFont' => 'sans-serif',
                'margin' => [15, 15, 15, 15],
            ]);
    }
}
