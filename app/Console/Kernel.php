<?php

namespace App\Console;

use App\Http\Controllers\PreController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */



     protected function schedule(Schedule $schedule)
     {
         $schedule->command('presence:auto-absences')->dailyAt('18:45');
        // $schedule->command('presence:auto-absences')->everyMinute();

        // Rappel pour les employés qui n'ont pas marqué leur arrivée (9h45)
        $schedule->command('presence:send-arrival-reminder')->dailyAt('09:45');
        
        // Notification aux administrateurs et superviseurs des employés sans arrivée (10h00)
        $schedule->command('presence:handle-missing-arrival')->dailyAt('10:00');
        
        // Rappel pour les employés qui n'ont pas marqué leur départ (18h15)
        $schedule->command('presence:send-departure-reminder')->dailyAt('18:15');
        
        // Notification aux administrateurs et superviseurs des employés sans départ (18h31)
        $schedule->command('presence:handle-missing-departure')->dailyAt('18:31');
     }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
