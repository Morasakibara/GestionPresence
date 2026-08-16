<?php

namespace App\Console\Commands;

use App\Models\Utilisateur;
use App\Services\EvaluationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlerteEvaluationsRouges extends Command
{
    protected $signature = 'presence:alertes-evaluations-rouges {--mois= : Mois au format Y-m (défaut : mois précédent)}';

    protected $description = 'Notifie l\'administrateur principal des employés en évaluation rouge (discipline critique).';

    public function handle(): int
    {
        $mois = $this->option('mois') ?: now()->subMonth()->format('Y-m');
        $debut = $mois . '-01';
        $fin = now()->parse($debut)->endOfMonth()->toDateString();

        $employes = DB::table('employer')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->select('employer.id', 'utilisateur.nom')
            ->get();

        $rouges = [];
        foreach ($employes as $employe) {
            $evaluation = EvaluationService::evaluer($employe->id, $debut, $fin);
            if ($evaluation['couleur'] === 'rouge') {
                $rouges[] = ['nom' => $employe->nom, 'note' => $evaluation['note']];
            }
        }

        $adminPrincipal = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();

        if (empty($rouges)) {
            $this->info("Aucun employé en évaluation rouge pour {$mois}.");

            return self::SUCCESS;
        }

        if (!$adminPrincipal) {
            $this->error('Aucun administrateur trouvé pour la notification.');

            return self::FAILURE;
        }

        foreach ($rouges as $r) {
            $adminPrincipal->notify(new \App\Notifications\EvaluationRougeNotification($r['nom'], $mois, $r['note']));
        }

        $this->info(count($rouges) . ' employé(s) en évaluation rouge notifié(s) pour ' . $mois . '.');

        return self::SUCCESS;
    }
}
