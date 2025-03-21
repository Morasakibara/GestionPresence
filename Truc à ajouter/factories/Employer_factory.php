<?php

namespace Database\Factories;

use App\Models\Employer;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{
    protected $model = Employer::class;

    public function definition()
    {
        return [
            'id' => function () {
                return Utilisateur::factory()->create(['role' => 'Employer'])->id;
            },
            'Sup_id' => function () {
                return Superviseur::factory()->create()->id;
            },
            'poste' => 'Employer',
            'equipe' => $this->faker->word(),
        ];
    }
}
