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
     * Minutes travaillées entre l'arrivée et le départ (0 si invalide).
     *
     * @param  \DateTimeInterface|string|null  $heureArrivee
     * @param  \DateTimeInterface|string|null  $heureDepart
     */
    public static function minutesTravail($heureArrivee, $heureDepart): int
    {
        if (!$heureArrivee || !$heureDepart) {
            return 0;
        }
        try {
            $arrivee = \Illuminate\Support\Carbon::parse($heureArrivee);
            $depart = \Illuminate\Support\Carbon::parse($heureDepart);
            $minutes = (int) $arrivee->diffInMinutes($depart);

            return $minutes > 0 ? $minutes : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Durée de travail formatée (ex: "7h30") ou null si indisponible.
     *
     * @param  \DateTimeInterface|string|null  $heureArrivee
     * @param  \DateTimeInterface|string|null  $heureDepart
     */
    public static function dureeTravail($heureArrivee, $heureDepart): ?string
    {
        $minutes = self::minutesTravail($heureArrivee, $heureDepart);
        if ($minutes <= 0) {
            return null;
        }

        $heures = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return $heures . 'h' . str_pad((string) $mins, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Formate un total de minutes en durée (ex: 450 -> "7h30").
     */
    public static function formaterDureeTotale(int $minutes): string
    {
        $heures = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return $heures . 'h' . str_pad((string) $mins, 2, '0', STR_PAD_LEFT);
    }

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

    /**
     * Historique mensuel de la note d'un employé sur les $mois derniers mois.
     * Retourne un tableau de ['mois' => 'Y-m', 'label' => 'Mois', 'note' => float, 'couleur' => string].
     */
    public static function historiqueMensuel(int $employerID, int $mois = 6): array
    {
        $historique = [];
        for ($i = $mois - 1; $i >= 0; $i--) {
            $moisDate = now()->subMonths($i);
            $eval = self::evaluer(
                $employerID,
                $moisDate->copy()->startOfMonth()->toDateString(),
                $moisDate->copy()->endOfMonth()->toDateString()
            );
            $historique[] = [
                'mois' => $moisDate->format('Y-m'),
                'label' => ucfirst($moisDate->locale('fr')->isoFormat('MMMM')),
                'note' => $eval['note'],
                'couleur' => $eval['couleur'],
            ];
        }

        return $historique;
    }

    /**
     * Évolution mensuelle de la note moyenne sur les $mois derniers mois.
     *
     * @param  array  $employerIds  IDs des employés (null = tout le monde)
     * @return array{labels: string[], notes: float[], couleurs: string[]}
     */
    public static function evolutionMensuelle(?array $employerIds = null, int $mois = 6): array
    {
        $labels = [];
        $notes = [];
        $couleurs = [];

        for ($i = $mois - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth()->toDateString();
            $end = now()->subMonths($i)->endOfMonth()->toDateString();

            $query = Presence::whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
            if ($employerIds !== null) {
                $query->whereIn('employerID', $employerIds);
            }
            $presences = $query->get();

            if ($presences->isEmpty()) {
                $labels[] = ucfirst(now()->subMonths($i)->translatedFormat('M Y'));
                $notes[] = 0;
                $couleurs[] = '#888888';
                continue;
            }

            $ids = $presences->pluck('employerID')->unique();
            $total = 0;
            $count = 0;
            foreach ($ids as $id) {
                $eval = self::evaluer((int) $id, $start, $end);
                if ($eval['note'] > 0) {
                    $total += $eval['note'];
                    $count++;
                }
            }

            $moyenne = $count > 0 ? round($total / $count, 1) : 0;
            $labels[] = ucfirst(now()->subMonths($i)->translatedFormat('M Y'));
            $notes[] = $moyenne;
            $couleurs[] = $moyenne >= 14 ? '#2E8B57' : ($moyenne >= 10 ? '#D97706' : '#D64545');
        }

        return [
            'labels' => $labels,
            'notes' => $notes,
            'couleurs' => $couleurs,
        ];
    }

    /**
     * Notes mensuelles par employé sur les $mois derniers mois (pour tableau comparatif).
     *
     * @param  array  $employerIds  IDs des employés
     * @return array  Liste de ['employerID', 'nom', 'notes' => [mois => note], 'moyenne', 'couleur']
     */
    public static function notesParEmploye(array $employerIds, int $mois = 6): array
    {
        if (empty($employerIds)) {
            return [];
        }

        $moisListe = [];
        for ($i = $mois - 1; $i >= 0; $i--) {
            $moisListe[] = [
                'mois' => now()->subMonths($i)->format('Y-m'),
                'label' => ucfirst(now()->subMonths($i)->translatedFormat('M Y')),
                'debut' => now()->subMonths($i)->startOfMonth()->toDateString(),
                'fin' => now()->subMonths($i)->endOfMonth()->toDateString(),
            ];
        }

        $noms = \App\Models\Utilisateur::whereIn('id', $employerIds)->pluck('nom', 'id')->toArray();
        $resultats = [];

        foreach ($employerIds as $id) {
            $notes = [];
            foreach ($moisListe as $m) {
                $eval = self::evaluer((int) $id, $m['debut'], $m['fin']);
                $notes[$m['mois']] = [
                    'note' => $eval['note'],
                    'couleur' => $eval['couleur'],
                ];
            }
            $valeurs = array_values(array_filter(array_column($notes, 'note'), fn ($n) => $n > 0));
            $moyenne = count($valeurs) > 0 ? round(array_sum($valeurs) / count($valeurs), 1) : 0;

            $resultats[] = [
                'employerID' => (int) $id,
                'nom' => $noms[$id] ?? ('Employé #' . $id),
                'notes' => $notes,
                'moisListe' => $moisListe,
                'moyenne' => $moyenne,
                'couleur' => self::couleurPourNote($moyenne),
            ];
        }

        // Tri par moyenne décroissante
        usort($resultats, fn ($a, $b) => $b['moyenne'] <=> $a['moyenne']);

        return $resultats;
    }

    /**
     * Statistiques d'une série d'évolution : moyenne globale et tendance.
     *
     * @param  array  $evolution  Sortie de evolutionMensuelle()
     * @return array{moyenne: float, tendance: string, delta: float, dernier: float, avant: float}
     */
    public static function statsEvolution(array $evolution): array
    {
        $notes = array_values(array_filter($evolution['notes'] ?? [], fn ($n) => $n > 0));
        $moyenne = count($notes) > 0 ? round(array_sum($notes) / count($notes), 1) : 0;

        $toutes = $evolution['notes'] ?? [];
        $dernier = (float) (end($toutes) ?: 0);
        $avant = (float) (count($toutes) >= 2 ? $toutes[count($toutes) - 2] : 0);

        if ($dernier <= 0 || $avant <= 0) {
            $tendance = 'stable';
        } elseif ($dernier > $avant) {
            $tendance = 'hausse';
        } elseif ($dernier < $avant) {
            $tendance = 'baisse';
        } else {
            $tendance = 'stable';
        }

        return [
            'moyenne' => $moyenne,
            'tendance' => $tendance,
            'delta' => round($dernier - $avant, 1),
            'dernier' => $dernier,
            'avant' => $avant,
        ];
    }

    /**
     * Calcule les axes de compétences (0-1) pour un employé sur une période.
     *
     * @return array{labels: string[], valeurs: float[]}
     */
    public static function competences(int $employerID, string $debut, string $fin): array
    {
        $stats = self::statsPeriode($employerID, $debut, $fin);
        $joursOuvres = max(1, (int) \Carbon\Carbon::parse($debut)->diffInDays(\Carbon\Carbon::parse($fin)) + 1);
        $presences = max(1, $stats['presences_completes']);

        $labels = ['Présence', 'Ponctualité', 'Discipline', 'Rendement', 'Assiduité'];
        $valeurs = [
            min(1, $stats['presences_completes'] / max(1, $joursOuvres)),
            $presences > 0 ? min(1, 1 - ($stats['retards'] / $presences)) : 0,
            $presences > 0 ? min(1, 1 - ($stats['suspectes'] / $presences)) : 0,
            min(1, $stats['rendements_remplis'] / max(1, $presences)),
            min(1, 1 - ($stats['absences'] / max(1, $joursOuvres))),
        ];

        return ['labels' => $labels, 'valeurs' => $valeurs];
    }

    /**
     * Génère un graphique radar SVG (compatible DomPDF).
     *
     * @param  array  $comp  Sortie de competences()
     * @param  int    $taille  Taille du canvas (carré)
     * @return string  HTML du SVG
     */
    public static function radarSvg(array $comp, int $taille = 200): string
    {
        $cx = $taille / 2;
        $cy = $taille / 2;
        $rayon = $taille * 0.38;
        $n = count($comp['labels']);
        $angles = [];
        for ($i = 0; $i < $n; $i++) {
            $angles[] = (2 * M_PI * $i / $n) - (M_PI / 2);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $taille . '" height="' . ($taille + 30) . '" viewBox="0 0 ' . $taille . ' ' . ($taille + 30) . '">';

        // Grilles concentriques (25%, 50%, 75%, 100%)
        for ($level = 1; $level <= 4; $level++) {
            $r = $rayon * ($level / 4);
            $points = [];
            for ($i = 0; $i < $n; $i++) {
                $points[] = round($cx + $r * cos($angles[$i]), 1) . ',' . round($cy + $r * sin($angles[$i]), 1);
            }
            $svg .= '<polygon points="' . implode(' ', $points) . '" fill="none" stroke="#E5E7EB" stroke-width="0.8"/>';
        }

        // Axes
        for ($i = 0; $i < $n; $i++) {
            $x = round($cx + $rayon * cos($angles[$i]), 1);
            $y = round($cy + $rayon * sin($angles[$i]), 1);
            $svg .= '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . $x . '" y2="' . $y . '" stroke="#D1D5DB" stroke-width="0.6"/>';
        }

        // Polygone des valeurs
        $polyPoints = [];
        for ($i = 0; $i < $n; $i++) {
            $v = max(0, min(1, $comp['valeurs'][$i]));
            $px = round($cx + $rayon * $v * cos($angles[$i]), 1);
            $py = round($cy + $rayon * $v * sin($angles[$i]), 1);
            $polyPoints[] = $px . ',' . $py;
        }
        $svg .= '<polygon points="' . implode(' ', $polyPoints) . '" fill="rgba(211,155,35,0.18)" stroke="#D39B23" stroke-width="2"/>';

        // Points et labels
        for ($i = 0; $i < $n; $i++) {
            $v = max(0, min(1, $comp['valeurs'][$i]));
            $px = round($cx + $rayon * $v * cos($angles[$i]), 1);
            $py = round($cy + $rayon * $v * sin($angles[$i]), 1);
            $svg .= '<circle cx="' . $px . '" cy="' . $py . '" r="3" fill="#D39B23" stroke="#fff" stroke-width="1.2"/>';

            // Labels autour
            $lx = round($cx + ($rayon + 18) * cos($angles[$i]), 1);
            $ly = round($cy + ($rayon + 18) * sin($angles[$i]), 1);
            $anchor = 'middle';
            if (cos($angles[$i]) > 0.3) $anchor = 'start';
            elseif (cos($angles[$i]) < -0.3) $anchor = 'end';
            $dy = '0.35em';
            if (sin($angles[$i]) < -0.3) $dy = '0em';
            elseif (sin($angles[$i]) > 0.3) $dy = '0.7em';
            $score = round($v * 20, 0);
            $svg .= '<text x="' . $lx . '" y="' . $ly . '" text-anchor="' . $anchor . '" dy="' . $dy . '" font-size="8" fill="#374151" font-weight="bold">' . htmlspecialchars($comp['labels'][$i]) . ' ' . $score . '/20</text>';
        }

        $svg .= '</svg>';

        return $svg;
    }
}
