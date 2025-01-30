<?php

namespace Database\Seeders;

use App\Models\Employer;
use Illuminate\Database\Seeder;

class EmployerSeeder extends Seeder
{
   
    public function run(): void
    {
        $employer=Employer::factory()->count(20)->create();
        echo '20 employer ajouter';
    }
}
