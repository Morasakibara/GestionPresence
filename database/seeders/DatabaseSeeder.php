<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Administrateur;
use App\Models\Employer;
use App\Models\Marquer;
use App\Models\Presence;
use App\Models\Rapport;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            UtilisateurSeeder::class,
            AdministrateurSeeder::class,
            SuperviseurSeeder::class,
            EmployerSeeder::class,
            PresenceSeeder::class,
            MarquerSeeder::class,
            RapportSeeder::class,
            WorkplaceLocationSeeder::class,
        ]);
    }
}
