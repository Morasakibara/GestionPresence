<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\Presence;
use Illuminate\Support\Facades\DB;

/**
 * Système d'évaluation de la discipline et du rendement.
 *
 * Une note sur 20 est calculée automatiquement à partir des données de présence
 * (retards, absences, présences complètes, fiches de rendement remplies).
 * La couleur associée : vert (≥ 14), orange (10 à 13), rouge (< 10).
 * Une évaluation enregistrée manuellement (admin/superviseur) prime sur le calcul.
 */
class EvaluationService
{
    public const VERT = 'vert';
    public const ORANGE = 'orange';
    public const ROUGE = 'rouge';

    /**
     * Calcule la couleur à partir d'une note sur 20.
     */
    public static function couleurPourNote(float $note): string
    {
        if ($note >= 14) {
            return self::VERT;
        }
        if ($note >= 10) {
            return self::ORANGE;
        }

        return self::ROUGE;
    }

    /**
     * Évalue un employé sur une période (dates inclusives).
     * Retourne ['note' => float, 'couleur' => string, 'commentaire' => string, 'manuelle' => bool].
     */
    public static function evaluer(int $employerID, string $debut, string $fin): array
    {
        // Une évaluation manuelle enregistrée pour la même période prime
        $mois = substr($debut, 0, 7);
        $manuelle = Evaluation::where('employerID', $employerID)->where('mois', $mois)->first();

        $stats = self::statsPeriode($employerID, $debut, $fin);

        // Note de base : présence complète bien notée
        $note = 12.0;
        $note -= min(5, $stats['retards'] * 0.5);          // -0,5 par retard (max -5)
        $note -= min(6, $stats['absences'] * 1.5);          // -1,5 par absence (max -6)
        $note -= min(3, $stats['suspectes'] * 1);           // -1 par présence suspecte (max -3)
        $note += min(4, $stats['rendements_remplis'] * 0.5); // +0,5 par fiche de rendement (max +4)
        $note += min(2, $stats['presences_completes'] * 0.1); // +0,1 par présence complète (max +2)

        $note = max(0, min(20, round($note, 1)));

        if ($manuelle) {
            return [
                'note' => (float) $manuelle->note,
                'couleur' => $manuelle->couleur,
                'commentaire' => $manuelle->commentaire,
                'manuelle' => true,
            ];
        }

        $commentaire = self::commentaireAuto($note, $stats);

        return [
            'note' => $note,
            'couleur' => self::couleurPourNote($note),
            'commentaire' => $commentaire,
            'manuelle' => false,
        ];
    }

    /**
     * Statistiques de la période pour un employé.
     */
    public static function statsPeriode(int $employerID, string $debut, string $fin): array
    {
        $presences = Presence::where('employerID', $employerID)
            ->whereDate('date', '>=', $debut)
            ->whereDate('date', '<=', $fin)
            ->get();

        $retards = $presences->filter(fn ($p) => $p->heureArrivee && now()->parse($p->heureArrivee)->format('H:i') > '08:00')->count();
        $absences = $presences->where('status', 'Absent')->count();
        $suspectes = $presences->where('suspect', true)->count();
        $presencesCompletes = $presences->where('status', 'présent')->count();
        $rendementsRemplis = $presences->whereNotNull('rendement')->where('rendement', '!=', '')->count();

        return [
            'retards' => $retards,
            'absences' => $absences,
            'suspectes' => $suspectes,
            'presences_completes' => $presencesCompletes,
            'rendements_remplis' => $rendementsRemplis,
            'total' => $presences->count(),
        ];
    }

    private static function commentaireAuto(float $note, array $stats): string
    {
        $parts = [];
        if ($stats['retards'] > 0) {
            $parts[] = $stats['retards'] . ' retard(s)';
        }
        if ($stats['absences'] > 0) {
            $parts[] = $stats['absences'] . ' absence(s)';
        }
        if ($stats['suspectes'] > 0) {
            $parts[] = $stats['suspectes'] . ' présence(s) suspecte(s)';
        }
        if ($stats['rendements_remplis'] > 0) {
            $parts[] = $stats['rendements_remplis'] . ' fiche(s) de rendement';
        }
        if (empty($parts)) {
            $parts[] = 'Aucun incident relevé';
        }

        return 'Évaluation automatique (' . implode(', ', $parts) . '). Note: ' . $note . '/20.';
    }
}
