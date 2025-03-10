<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PreController;

class HandleAutoAbsences extends Command
{
    protected $signature = 'presence:auto-absences';
    protected $description = 'Traite automatiquement les absences pour les employés';

    public function handle()
    {
        $controller = new PreController();
        $result = $controller->handleAutoAbsences();
        $this->info($result);

        return 0;
    }
}
