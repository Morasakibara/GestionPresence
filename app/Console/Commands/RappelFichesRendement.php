<?php

namespace App\Console\Commands;

use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RappelFichesRendement extends Command
{
    protected $signature = 'presence:rappel-fiches-rendement {--semaine= : Date de fin de semaine au format Y-m-d (défaut : aujourd\'hui)}';

    protected $description = 'Notifie chaque superviseur des membres de son équipe n\'ayant pas rempli leur fiche de rendement sur la semaine.';

    public function handle(): int
    {
        $fin = $this->option('semaine') ?: now()->toDateString();
        $debut = now()->parse($fin)->startOfWeek()->toDateString();
        $semaineLabel = now()->parse($debut)->format('d/m/Y') . ' au ' . now()->parse($fin)->format('d/m/Y');

        // Tous les superviseurs ayant une équipe
        $superviseurs = Superviseur::whereNotNull('equipe')->get();

        $totalNotifies = 0;

        foreach ($superviseurs as $sup) {
            $employerIds = DB::table('employer')->where('equipe', $sup->equipe)->pluck('id')->toArray();
            if (empty($employerIds)) {
                continue;
            }

            // Présences (travail effectué) sur la semaine par membre
            $presences = DB::table('presence')
                ->whereIn('employerID', $employerIds)
                ->whereDate('date', '>=', $debut)
                ->whereDate('date', '<=', $fin)
                ->select('employerID', DB::raw('count(*) as total'))
                ->groupBy('employerID')
                ->pluck('total', 'employerID');

            // Fiches remplies sur la semaine par membre
            $remplies = DB::table('presence')
                ->whereIn('employerID', $employerIds)
                ->whereDate('date', '>=', $debut)
                ->whereDate('date', '<=', $fin)
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->select('employerID', DB::raw('count(*) as total'))
                ->groupBy('employerID')
                ->pluck('total', 'employerID');

            // Membres ayant travaillé (au moins une présence) mais sans fiche complète
            $membresSansFiche = [];
            foreach ($employerIds as $id) {
                $aTravaille = ((int) ($presences[$id] ?? 0)) > 0;
                $rempliesCount = (int) ($remplies[$id] ?? 0);
                if ($aTravaille && $rempliesCount === 0) {
                    $membresSansFiche[] = ['nom' => $this->employeNom($id), 'manquantes' => 'au moins 1'];
                }
            }

            if (empty($membresSansFiche)) {
                continue;
            }

            $supUser = Utilisateur::find($sup->id);
            if (!$supUser) {
                continue;
            }

            $supUser->notify(new \App\Notifications\FicheRendementRappelNotification($membresSansFiche, $semaineLabel));
            $totalNotifies++;
        }

        if ($totalNotifies === 0) {
            $this->info('Aucun superviseur concerné : toutes les fiches de rendement de la semaine sont remplies.');

            return self::SUCCESS;
        }

        $this->info($totalNotifies . ' superviseur(s) notifié(s) pour les fiches de rendement manquantes (' . $semaineLabel . ').');

        return self::SUCCESS;
    }

    private function employeNom(int $id): string
    {
        $user = Utilisateur::find($id);

        return $user?->nom ?? 'Employé #' . $id;
    }
}
