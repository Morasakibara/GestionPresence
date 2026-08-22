<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CongeExportService
{
    /**
     * Export Excel des congés — pivot par employé et mois.
     */
    public static function pivot(string $mois = null): \Illuminate\Http\Response
    {
        $mois = $mois ?? now()->format('Y-m');
        $debut = Carbon::parse($mois . '-01');
        $fin = $debut->copy()->endOfMonth();

        // Récupérer tous les congés de la période (ou à cheval sur la période)
        $conges = Conge::with('employe')
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin', [$debut, $fin])
                  ->orWhere(function ($q2) use ($debut, $fin) {
                      $q2->where('date_debut', '<=', $debut)->where('date_fin', '>=', $fin);
                  });
            })
            ->get();

        // Regrouper par employé
        $parEmploye = $conges->groupBy('employer_id');

        $bom = "\xEF\xBB\xBF";
        $contenu = $bom;

        // En-tête
        $contenu .= "=LE PHARAON - Congés & Absences=\n";
        $contenu .= "=Pivot par employé — " . ucfirst($debut->locale('fr')->isoFormat('MMMM YYYY')) . "=\n";
        $contenu .= "=Date d'export : " . now()->format('d/m/Y H:i') . "=\n";
        $contenu .= "\n";

        // En-têtes
        $colonnes = ['Employé', 'Équipe', 'Total jours', 'Congés', 'Abs. justifiées', 'Abs. injustifiées', 'Détails'];
        $contenu .= implode(';', $colonnes) . "\n";

        foreach ($parEmploye as $employerId => $employeConges) {
            $nom = $employeConges->first()->employe->nom ?? 'N/A';
            $equipe = $employeConges->first()->employe->equipe ?? DB::table('employer')->where('id', $employerId)->value('equipe') ?? '—';

            $totalJours = 0;
            $congesCount = 0;
            $absJustifiees = 0;
            $absInjustifiees = 0;
            $details = [];

            foreach ($employeConges as $c) {
                $jours = $c->dureeJours();
                $totalJours += $jours;

                match ($c->type) {
                    'conge' => $congesCount += $jours,
                    'absence_justifiee' => $absJustifiees += $jours,
                    'absence_injustifiee' => $absInjustifiees += $jours,
                };

                $details[] = $c->typeLabel() . ' ' . $c->date_debut->format('d/m') . '-' . $c->date_fin->format('d/m') . ' [' . $c->statut . ']';
            }

            $ligne = [
                $nom,
                $equipe,
                $totalJours,
                $congesCount,
                $absJustifiees,
                $absInjustifiees,
                implode(' | ', $details),
            ];

            $contenu .= implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $ligne)) . "\n";
        }

        // Résumé
        $contenu .= "\n";
        $contenu .= "=RÉSUMÉ=\n";
        $contenu .= "Total demandes;" . $conges->count() . ";\n";
        $contenu .= "Approuvées;" . $conges->where('statut', 'approuve')->count() . ";\n";
        $contenu .= "En attente;" . $conges->where('statut', 'en_attente')->count() . ";\n";
        $contenu .= "Refusées;" . $conges->where('statut', 'refuse')->count() . ";\n";

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=conges_pivot_' . $mois . '.csv',
        ]);
    }

    /**
     * Export CSV de tous les congés avec filtres.
     */
    public static function liste(string $statut = null, string $equipe = null): \Illuminate\Http\Response
    {
        $query = Conge::with('employe', 'traitePar')->orderByDesc('date_debut');

        if ($statut) $query->where('statut', $statut);
        if ($equipe) {
            $equipeSup = \App\Models\Superviseur::where('equipe', $equipe)->pluck('id');
            $equipeEmployeIds = DB::table('employer')->whereIn('Sup_id', $equipeSup)->pluck('id');
            $query->whereIn('employer_id', $equipeEmployeIds);
        }

        $conges = $query->get();

        $bom = "\xEF\xBB\xBF";
        $contenu = $bom;
        $contenu .= "=LE PHARAON - Liste complète des congés=\n";
        $contenu .= "=Date : " . now()->format('d/m/Y H:i') . "=\n\n";

        $colonnes = ['Employé', 'Type', 'Du', 'Au', 'Jours', 'Motif', 'Statut', 'Traité par'];
        $contenu .= implode(';', $colonnes) . "\n";

        foreach ($conges as $c) {
            $ligne = [
                $c->employe->nom ?? 'N/A',
                $c->typeLabel(),
                $c->date_debut->format('d/m/Y'),
                $c->date_fin->format('d/m/Y'),
                $c->dureeJours(),
                $c->motif,
                $c->statut,
                $c->traitePar->nom ?? '—',
            ];
            $contenu .= implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $ligne)) . "\n";
        }

        $filename = 'conges_liste' . ($statut ? '_' . $statut : '') . ($equipe ? '_' . str_replace(' ', '_', $equipe) : '') . '.csv';

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }
}
