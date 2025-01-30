<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;
    
    public function definition()
    {
        $faker = Faker::create();

        return [
            'nom' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'motDePasse' => Hash::make('password'),
            'role' => $faker->randomElement(['Administrateur', 'Superviseur', 'Employer']),
        ];
    }
}

