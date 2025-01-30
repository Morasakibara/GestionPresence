<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\Employer;
use App\Models\Superviseur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperviseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superviseur=Superviseur::factory()->count(10)->create();
        
        echo '10 superviseures ajouter!';
    }
}
