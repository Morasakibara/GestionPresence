<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Notifications\SuspectRappelNotification;

class RemindUnprocessedSuspects extends Command
{
    protected $signature = 'presence:rappel-suspectes {--days= : Nombre de jours avant rappel (défaut : config)}';
    protected $description = 'Notifie l\'administrateur des présences suspectes non traitées depuis plus de X jours';

    public function handle()
    {
        $jours = (int) ($this->option('days') ?: config('geolocation.rappel_suspectes_jours', 7));

        // Présences suspectes encore non traitées (statut "nouveau") créées il y a plus de X jours
        $count = Presence::where('suspect', true)
            ->where('statut_traitement', 'nouveau')
            ->whereDate('created_at', '<=', now()->subDays($jours))
            ->count();

        if ($count === 0) {
            $this->info('Aucune présence suspecte en attente depuis plus de ' . $jours . ' jour(s).');

            return 0;
        }

        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();

        if (!$admin) {
            $this->error('Aucun administrateur trouvé pour recevoir le rappel.');

            return 1;
        }

        $admin->notify(new SuspectRappelNotification($count, $jours));
        $this->info("Rappel envoyé à l'administrateur : {$count} présence(s) suspecte(s) en attente depuis plus de {$jours} jour(s).");

        return 0;
    }
}
