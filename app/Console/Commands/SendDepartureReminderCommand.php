<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\Presence;
use App\Notifications\DepartureReminderNotification;
use Carbon\Carbon;

class SendDepartureReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:send-departure-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un rappel aux employés qui n\'ont pas encore marqué leur départ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ne pas exécuter pendant les week-ends (0 = dimanche, 6 = samedi)
        $today = Carbon::now();
        if ($today->dayOfWeek === 0 || $today->dayOfWeek === 6) {
            $this->info('Week-end: aucun rappel envoyé.');
            return 0;
        }

        $this->info('Envoi des rappels de départ...');

        // Récupérer tous les employés qui ont marqué leur arrivée mais pas leur départ
        $todayString = $today->toDateString();

        // Trouver les présences qui ont une heure d'arrivée mais pas de départ
        $presencesToday = Presence::whereDate('date', $todayString)
                                  ->whereNotNull('heureArrivee')
                                  ->whereNull('heureDepart')
                                  ->get();

        $notificationCount = 0;

        foreach ($presencesToday as $presence) {
            // Récupérer l'employé
            $employe = Utilisateur::find($presence->employerID);

            if ($employe) {
                $employe->notify(new DepartureReminderNotification());
                $notificationCount++;
                $this->info("Rappel de départ envoyé à {$employe->nom} ({$employe->email})");
            }
        }

        $this->info("Total des rappels de départ envoyés: {$notificationCount}");
        return 0;
    }
}
