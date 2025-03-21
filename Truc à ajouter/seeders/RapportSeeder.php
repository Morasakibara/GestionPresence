<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\Rapport;
use App\Models\Superviseur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RapportSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les administrateurs et superviseurs
        $administrateurs = Administrateur::all();
        $superviseurs = Superviseur::all();

        if ($administrateurs->isEmpty()) {
            $this->command->error("Aucun administrateur trouvé. Veuillez exécuter UtilisateurSeeder d'abord.");
            return;
        }

        if ($superviseurs->isEmpty()) {
            $this->command->error("Aucun superviseur trouvé. Veuillez exécuter UtilisateurSeeder d'abord.");
            return;
        }

        // Pour chaque superviseur, créer quelques rapports
        foreach ($superviseurs as $superviseur) {
            $adminCount = $administrateurs->count();

            // Créer 2 à 5 rapports par superviseur
            $reportsCount = mt_rand(2, 5);

            for ($i = 0; $i < $reportsCount; $i++) {
                $date = Carbon::now()->subDays(mt_rand(1, 90));
                $fileName = 'rapport_equipe_' . $superviseur->equipe . '_' . $date->format('Y_m_d_His') . '.pdf';
                Rapport::create([
                    'Adm_id' => $administrateurs[mt_rand(0, $adminCount - 1)]->id,
                    'Sup_id' => $superviseur->id,
                    'periode' => $date->format('Y-m-d H:i:s'),
                    'contenu' => 'rapports/' . $fileName,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('Rapports générés avec succès pour tous les superviseurs.');
    }
}
