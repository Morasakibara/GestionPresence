<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Notifications\MembresBloquesNotification;
use Illuminate\Support\Facades\DB;

class RemindBlockedMembers extends Command
{
    protected $signature = 'presence:rappel-blocages';
    protected $description = 'Notifie chaque superviseur tant que des membres de son équipe restent bloqués au pointage';

    public function handle()
    {
        $blocageMax = (int) config('geolocation.blocage_suspects_max', 3);
        $blocageJours = (int) config('geolocation.blocage_periode_jours', 30);

        // Tous les superviseurs
        $superviseurs = Utilisateur::where('role', 'Superviseur')->get();

        $notificationsEnvoyees = 0;

        foreach ($superviseurs as $superviseur) {
            $superviseurInfo = DB::table('Superviseur')->where('id', $superviseur->id)->first();
            if (!$superviseurInfo || !$superviseurInfo->equipe) {
                continue;
            }

            $membresEquipe = DB::table('employer')->where('equipe', $superviseurInfo->equipe)->pluck('id')->toArray();
            if (empty($membresEquipe)) {
                continue;
            }

            // Membres bloqués de l'équipe
            $membresBloques = [];
            foreach ($membresEquipe as $membreId) {
                $count = Presence::where('employerID', $membreId)
                    ->where('suspect', true)
                    ->where('statut_traitement', '!=', 'justifié')
                    ->whereDate('date', '>=', now()->subDays($blocageJours))
                    ->count();

                if ($count >= $blocageMax) {
                    $nom = DB::table('utilisateur')->where('id', $membreId)->value('nom') ?? 'Employé #' . $membreId;
                    $membresBloques[] = ['nom' => $nom, 'suspects' => $count];
                }
            }

            if (empty($membresBloques)) {
                continue;
            }

            $superviseur->notify(new MembresBloquesNotification($membresBloques));
            $notificationsEnvoyees++;
        }

        if ($notificationsEnvoyees === 0) {
            $this->info('Aucun membre bloqué — aucune notification envoyée.');

            return 0;
        }

        $this->info($notificationsEnvoyees . ' superviseur(s) notifié(s) : des membres restent bloqués au pointage.');

        return 0;
    }
}
