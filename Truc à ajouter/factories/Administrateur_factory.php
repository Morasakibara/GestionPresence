<?php

namespace Database\Factories;

use App\Models\Administrateur;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdministrateurFactory extends Factory
{
    protected $model = Administrateur::class;

    public function definition()
    {
        return [
            'id' => function () {
                return Utilisateur::factory()->create(['role' => 'Administrateur'])->id;
            },
            'poste' => $this->faker->jobTitle(),
        ];
    }
}
