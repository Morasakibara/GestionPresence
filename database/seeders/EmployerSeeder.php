<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class EmployerSeeder extends Seeder
{
    /**
     * Répartit les employés entre les superviseurs (équipe et Sup_id cohérents).
     */
    public function run(): void
    {
        $superviseurs = Superviseur::orderBy('id')->get();

        if ($superviseurs->isEmpty()) {
            throw new \Exception('Aucun superviseur trouvé. Lancez SuperviseurSeeder d\'abord.');
        }

        $postes = ['Développeur', 'Commercial', 'Comptable', 'Ressources Humaines', 'Support Technique'];
        $employers = Utilisateur::where('role', 'Employer')->orderBy('id')->get();

        foreach ($employers as $i => $emp) {
            $superviseur = $superviseurs[$i % $superviseurs->count()];

            Employer::updateOrCreate(
                ['id' => $emp->id],
                [
                    'Sup_id' => $superviseur->id,
                    'poste' => $postes[$i % count($postes)],
                    'equipe' => $superviseur->equipe,
                ]
            );
        }

        $this->command->info($employers->count() . ' employés répartis dans ' . $superviseurs->count() . ' équipes.');
    }
}
