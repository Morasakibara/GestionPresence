<?php

namespace Database\Seeders;

use App\Models\Marquer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarquerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marquer=Marquer::factory()->count(100)->create();
        echo '100 presence marquer';
    }
}
