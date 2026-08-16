<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\Rapport;
use App\Models\Superviseur;
use Illuminate\Database\Seeder;

class RapportSeeder extends Seeder
{
    /**
     * Crée quelques rapports de présence générés par l'administrateur.
     */
    public function run(): void
    {
        $admin = Administrateur::first();
        $superviseur = Superviseur::first();

        if (!$admin) {
            $this->command->warn('Aucun administrateur trouvé, rapports ignorés.');
            return;
        }

        $rapports = [
            ['periode' => '2026-08-01 au 2026-08-07', 'contenu' => 'rapports/rapport_presence_2026_08_01_2026_08_07.pdf'],
            ['periode' => '2026-08-08 au 2026-08-14', 'contenu' => 'rapports/rapport_presence_2026_08_08_2026_08_14.pdf'],
        ];

        foreach ($rapports as $data) {
            Rapport::create([
                'Adm_id' => $admin->id,
                'Sup_id' => $superviseur->id ?? null,
                'periode' => $data['periode'],
                'contenu' => $data['contenu'],
            ]);
        }

        $this->command->info(count($rapports) . ' rapports créés.');
    }
}
