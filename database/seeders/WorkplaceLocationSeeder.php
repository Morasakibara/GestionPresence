<?php

namespace Database\Seeders;

use App\Models\WorkplaceLocation;
use Illuminate\Database\Seeder;

class WorkplaceLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'nom' => 'Siège social - Paris',
                'latitude' => 48.856613,
                'longitude' => 2.352222,
                'rayon' => 100,
                'actif' => true,
            ],
            [
                'nom' => 'Agence Lyon',
                'latitude' => 45.764043,
                'longitude' => 4.835659,
                'rayon' => 150,
                'actif' => true,
            ],
            [
                'nom' => 'Bureau Marseille',
                'latitude' => 43.296482,
                'longitude' => 5.369780,
                'rayon' => 100,
                'actif' => false,
            ],
        ];

        foreach ($locations as $location) {
            WorkplaceLocation::updateOrCreate(
                ['nom' => $location['nom']],
                $location
            );
        }

        $this->command->info(count($locations) . ' lieux de travail ajoutés.');
    }
}
