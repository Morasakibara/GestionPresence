<?php

namespace Database\Seeders;

use App\Models\WorkplaceLocation;
use Illuminate\Database\Seeder;

class WorkplaceLocationSeeder extends Seeder
{
    public function run()
    {
        // Créer quelques emplacements de travail
        WorkplaceLocation::create([
            'nom' => 'Siège social',
            'latitude' => 14.6937,
            'longitude' => -17.4441,  // Coordonnées de Dakar
            'rayon' => 100,
            'actif' => true
        ]);

        WorkplaceLocation::create([
            'nom' => 'Bureau annexe',
            'latitude' => 14.7648,
            'longitude' => -17.3661,  // Coordonnées aux alentours de Dakar
            'rayon' => 80,
            'actif' => true
        ]);

        WorkplaceLocation::create([
            'nom' => 'Centre de formation',
            'latitude' => 14.7167,
            'longitude' => -17.4677,  // Coordonnées aux alentours de Dakar
            'rayon' => 150,
            'actif' => true
        ]);

        $this->command->info('3 emplacements de travail créés avec succès.');
    }
}
