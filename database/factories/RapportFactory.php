<?php
namespace Database\Factories;

use App\Models\Rapport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Superviseur;
use App\Models\Administrateur;

class RapportFactory extends Factory
{
    protected $model = Rapport::class;

    public function definition()
    {
        $faker = Faker::create();

        return [
            'Adm_id' => Administrateur::factory()->create()->id,
            'Sup_id' => Superviseur::factory()->create()->id,
            'periode' => $faker->dateTimeBetween('-1 year', 'now'),
            'contenu' => $faker->paragraph,

        ];
    }
}