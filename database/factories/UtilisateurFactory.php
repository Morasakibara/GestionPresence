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

        // unique() n'est garanti que par instance Faker : on ajoute un suffixe
        // aléatoire pour garantir l'unicité entre les centaines d'utilisateurs
        // créés par les factories imbriquées des seeders.
        return [
            'nom' => $faker->name,
            'email' => preg_replace('/@/', uniqid() . '@', $faker->safeEmail),
            'motDePasse' => Hash::make('password'),
            'role' => $faker->randomElement(['Administrateur', 'Superviseur', 'Employer']),
        ];
    }
}

