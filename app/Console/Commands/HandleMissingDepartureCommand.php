<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Models\Employer;
use App\Models\Presence;
use App\Notifications\MissingDepartureNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleMissingDepartureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:handle-missing-departure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifie les administrateurs et superviseurs des employés sans départ marqué à 18h31';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ne pas exécuter pendant les week-ends (0 = dimanche, 6 = samedi)
        $today = Carbon::now();
        if ($today->dayOfWeek === 0 || $today->dayOfWeek === 6) {
            $this->info('Week-end: aucune notification envoyée.');
            return 0;
        }

        $this->info('Traitement des absences de départ...');

        // Récupérer tous les administrateurs
        $admins = Utilisateur::where('role', 'administrateur')
                            ->orWhere('role', 'Administrateur')
                            ->orWhere('role', 'ADMINISTRATEUR')
                            ->orWhere('role', 'Admin')
                            ->get();

        // Trouver les présences qui ont une heure d'arrivée mais pas de départ
        $todayString = $today->toDateString();
        $presencesToday = Presence::whereDate('date', $todayString)
                                  ->whereNotNull('heureArrivee')
                                  ->whereNull('heureDepart')
                                  ->get();

        $notificationCount = 0;

        foreach ($presencesToday as $presence) {
            // Récupérer l'employé
            $employe = Utilisateur::find($presence->employerID);

            if ($employe) {
                // Récupérer les informations de l'employé
                $employerInfo = Employer::where('id', $employe->id)->first();

                // Notifier les administrateurs
                foreach ($admins as $admin) {
                    $admin->notify(new MissingDepartureNotification($employe, $todayString));
                    $notificationCount++;
                }

                // Notifier le superviseur direct si disponible
                if ($employerInfo && $employerInfo->Sup_id) {
                    $superviseur = Utilisateur::find($employerInfo->Sup_id);
                    if ($superviseur) {
                        $superviseur->notify(new MissingDepartureNotification($employe, $todayString));
                        $notificationCount++;
                    }
                }

                $this->info("Notifications d'absence de départ envoyées pour {$employe->nom}");
            }
        }

        $this->info("Total des notifications d'absence de départ envoyées: {$notificationCount}");
        return 0;
    }
}
