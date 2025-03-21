<?php

namespace Database\Factories;

use App\Models\Administrateur;
use App\Models\Rapport;
use App\Models\Superviseur;
use Illuminate\Database\Eloquent\Factories\Factory;

class RapportFactory extends Factory
{
    protected $model = Rapport::class;

    public function definition()
    {
        return [
            'Adm_id' => function () {
                return Administrateur::factory()->create()->id;
            },
            'Sup_id' => function () {
                return Superviseur::factory()->create()->id;
            },
            'periode' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
            'contenu' => 'rapports/' . $this->faker->word() . '.pdf',
        ];
    }
}
