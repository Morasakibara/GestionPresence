<?php

namespace Database\Factories;

use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuperviseurFactory extends Factory
{
    protected $model = Superviseur::class;

    public function definition()
    {
        return [
            'id' => function () {
                return Utilisateur::factory()->create(['role' => 'Superviseur'])->id;
            },
            'equipe' => $this->faker->word(),
        ];
    }
}
