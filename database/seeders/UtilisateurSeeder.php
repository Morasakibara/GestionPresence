<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class UtilisateurSeeder extends Seeder
{
    /**
     * Crée les utilisateurs de démonstration (1 admin, 3 superviseurs, 15 employés).
     * Tous les comptes ont pour mot de passe : password
     */
    public function run(): void
    {
        $utilisateurs = [
            // Administrateurs
            ['nom' => 'Administrateur Principal', 'email' => 'admin@3hcig.com', 'role' => 'Administrateur'],
            ['nom' => 'Administratrice Adjointe', 'email' => 'admin2@3hcig.com', 'role' => 'Administrateur'],
            // Superviseurs
            ['nom' => 'Marie Dupont', 'email' => 'superviseur.alpha@3hcig.com', 'role' => 'Superviseur'],
            ['nom' => 'Jean Kouassi', 'email' => 'superviseur.beta@3hcig.com', 'role' => 'Superviseur'],
            ['nom' => 'Aline Mbarga', 'email' => 'superviseur.gamma@3hcig.com', 'role' => 'Superviseur'],
        ];

        // 15 employés
        $prenoms = ['Paul', 'Claire', 'Luc', 'Sonia', 'David', 'Emma', 'Karim', 'Nadia', 'Thomas', 'Awa', 'Marc', 'Léa', 'Boris', 'Inès', 'Hugo'];
        $noms = ['Martin', 'Nkolo', 'Bernard', 'Fokou', 'Petit', 'Ngono', 'Robert', 'Tchoupo', 'Durand', 'Bella', 'Leroy', 'Mballa', 'Garcia', 'Ateba', 'Moreau'];

        foreach ($prenoms as $i => $prenom) {
            $utilisateurs[] = [
                'nom' => $prenom . ' ' . $noms[$i],
                'email' => 'employe.' . ($i + 1) . '@3hcig.com',
                'role' => 'Employer',
            ];
        }

        foreach ($utilisateurs as $data) {
            Utilisateur::create([
                'nom' => $data['nom'],
                'email' => $data['email'],
                'motDePasse' => 'password', // hashé automatiquement par le modèle
                'role' => $data['role'],
            ]);
        }

        $this->command->info(count($utilisateurs) . ' utilisateurs créés (mot de passe : password).');
    }
}
