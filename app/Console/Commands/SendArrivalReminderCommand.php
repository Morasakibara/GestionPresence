<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\Presence;
use App\Notifications\ArrivalReminderNotification;
use Carbon\Carbon;

class SendArrivalReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:send-arrival-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un rappel aux employés qui n\'ont pas encore marqué leur arrivée';

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

        $this->info('Envoi des rappels d\'arrivée...');

        // Récupérer tous les employés
        $employes = Utilisateur::where('role', 'Employer')->get();

        $todayString = $today->toDateString();
        $notificationCount = 0;

        foreach ($employes as $employe) {
            // Vérifier si l'employé a déjà marqué son arrivée aujourd'hui
            $presenceToday = Presence::where('employerID', $employe->id)
                                     ->whereDate('date', $todayString)
                                     ->whereNotNull('heureArrivee')
                                     ->first();

            // Si l'employé n'a pas encore marqué sa présence, envoyer un rappel
            if (!$presenceToday) {
                $employe->notify(new ArrivalReminderNotification());
                $notificationCount++;
                $this->info("Rappel d'arrivée envoyé à {$employe->nom} ({$employe->email})");
            }
        }

        $this->info("Total des rappels d'arrivée envoyés: {$notificationCount}");
        return 0;
    }
}
