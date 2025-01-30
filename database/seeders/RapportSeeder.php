<?php

namespace Database\Seeders;

use App\Models\Rapport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RapportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rapport=Rapport::factory()->count(50)->create();
        echo '50 rapport ajouter avec succes';
    }
}
