<?php

namespace Database\Factories;

use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class SuperviseurFactory extends Factory
{
    protected $model = Superviseur::class;

    public function definition()
    {
        $faker = Faker::create();

        return [
            'id' => Utilisateur::factory()->create()->id,
            'equipe' => $faker->word,
        ];
    }
}

