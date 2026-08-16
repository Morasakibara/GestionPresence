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
        // Résolution via le conteneur pour injecter les dépendances du contrôleur
        $controller = app(PreController::class);
        $result = $controller->handleAutoAbsences();
        $this->info($result);

        return 0;
    }
}
