<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use App\Models\Administrateur;
use App\Models\Superviseur;
use App\Models\Employer;
use Illuminate\Database\Seeder;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 5 administrateurs
        Utilisateur::factory()->count(5)->create([
            'role' => 'administrateur'
        ])->each(function ($user) {
            Administrateur::factory()->create([
                'id' => $user->id,
            ]);
        });

        // Créer 10 superviseurs
        Utilisateur::factory()->count(10)->create([
            'role' => 'Superviseur'
        ])->each(function ($user) {
            Superviseur::factory()->create([
                'id' => $user->id,
                'equipe' => 'Equipe ' . $user->id
            ]);
        });

        // Créer 30 employés, chacun rattaché à un superviseur aléatoire
        $superviseurs = Superviseur::all();

        Utilisateur::factory()->count(30)->create([
            'role' => 'Employer'
        ])->each(function ($user) use ($superviseurs) {
            Employer::factory()->create([
                'id' => $user->id,
                'Sup_id' => $superviseurs->random()->id,
                'equipe' => $superviseurs->random()->equipe
            ]);
        });

        $this->command->info('45 utilisateurs créés avec succès: 5 administrateurs, 10 superviseurs et 30 employés.');
    }
}
