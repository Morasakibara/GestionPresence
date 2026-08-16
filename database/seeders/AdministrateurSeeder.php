<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class AdministrateurSeeder extends Seeder
{
    /**
     * Crée les lignes "administrateur" pour les utilisateurs au rôle Administrateur.
     */
    public function run(): void
    {
        $admins = Utilisateur::where('role', 'Administrateur')->get();

        foreach ($admins as $admin) {
            Administrateur::updateOrCreate(
                ['id' => $admin->id],
                ['poste' => 'Directeur Général']
            );
        }

        $this->command->info($admins->count() . ' administrateurs créés.');
    }
}
