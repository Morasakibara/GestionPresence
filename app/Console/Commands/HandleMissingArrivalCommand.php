<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Models\Employer;
use App\Models\Presence;
use App\Notifications\MissingArrivalNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleMissingArrivalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:handle-missing-arrival';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifie les administrateurs et superviseurs des employés sans arrivée marquée à 10h';

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

        $this->info('Traitement des absences d\'arrivée...');

        // Récupérer tous les employés
        $employes = Utilisateur::where('role', 'Employer')->get();

        // Récupérer tous les administrateurs
        $admins = Utilisateur::where('role', 'administrateur')
                            ->orWhere('role', 'Administrateur')
                            ->orWhere('role', 'ADMINISTRATEUR')
                            ->orWhere('role', 'Admin')
                            ->get();

        // Récupérer tous les superviseurs
        $superviseurs = Utilisateur::where('role', 'Superviseur')->get();

        $todayString = $today->toDateString();
        $notificationCount = 0;

        foreach ($employes as $employe) {
            // Vérifier si l'employé a déjà marqué son arrivée aujourd'hui
            $presenceToday = Presence::where('employerID', $employe->id)
                                     ->whereDate('date', $todayString)
                                     ->whereNotNull('heureArrivee')
                                     ->first();

            // Si l'employé n'a pas encore marqué sa présence
            if (!$presenceToday) {
                // Récupérer les informations de l'employé
                $employerInfo = Employer::where('id', $employe->id)->first();

                // Notifier les administrateurs
                foreach ($admins as $admin) {
                    $admin->notify(new MissingArrivalNotification($employe, $todayString));
                    $notificationCount++;
                }

                // Notifier le superviseur direct si disponible
                if ($employerInfo && $employerInfo->Sup_id) {
                    $superviseur = Utilisateur::find($employerInfo->Sup_id);
                    if ($superviseur) {
                        $superviseur->notify(new MissingArrivalNotification($employe, $todayString));
                        $notificationCount++;
                    }
                }

                $this->info("Notifications d'absence d'arrivée envoyées pour {$employe->nom}");
            }
        }

        $this->info("Total des notifications d'absence d'arrivée envoyées: {$notificationCount}");
        return 0;
    }
}
