<?php

namespace Database\Seeders;

use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class SuperviseurSeeder extends Seeder
{
    /**
     * Crée les lignes "superviseur" avec une équipe nommée pour chaque superviseur.
     */
    public function run(): void
    {
        $equipes = ['Équipe Alpha', 'Équipe Beta', 'Équipe Gamma'];
        $superviseurs = Utilisateur::where('role', 'Superviseur')->orderBy('id')->get();

        foreach ($superviseurs as $i => $sup) {
            Superviseur::updateOrCreate(
                ['id' => $sup->id],
                ['equipe' => $equipes[$i % count($equipes)]]
            );
        }

        $this->command->info($superviseurs->count() . ' superviseurs créés.');
    }
}
