<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use Illuminate\Support\Facades\Hash;

class SuperviseursSpecialisesSeeder extends Seeder
{
    public function run(): void
    {
        $superviseurs = [
            [
                'nom' => 'Directrice Le Pharaon',
                'email' => 'directrice@lepharaon.com',
                'password' => 'password123',
                'type' => Superviseur::TYPE_DIRECTRICE,
                'equipe' => 'Administration',
            ],
            [
                'nom' => 'Secrétaire Le Pharaon',
                'email' => 'secretaire@lepharaon.com',
                'password' => 'password123',
                'type' => Superviseur::TYPE_SECRETAIRE,
                'equipe' => 'Services Photo',
            ],
            [
                'nom' => 'Gestionnaire Stock Le Pharaon',
                'email' => 'stock@lepharaon.com',
                'password' => 'password123',
                'type' => Superviseur::TYPE_GESTIONNAIRE_STOCK,
                'equipe' => 'Gestion Stock',
            ],
        ];

        foreach ($superviseurs as $data) {
            $user = Utilisateur::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom' => $data['nom'],
                    'motDePasse' => Hash::make($data['password']),
                    'role' => 'Superviseur',
                ]
            );

            Superviseur::firstOrCreate(
                ['id' => $user->id],
                [
                    'equipe' => $data['equipe'],
                    'type_superviseur' => $data['type'],
                ]
            );

            $this->command->info("  ✓ {$data['nom']} ({$data['email']}) — {$data['type']}");
        }
    }
}
