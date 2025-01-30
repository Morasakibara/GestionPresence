<?php

namespace Database\Factories;
use App\Models\Administrateur;
use App\Models\Utilisateur;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrateur>
 */
class AdministrateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */#
    protected $model = Administrateur::class;

    public function definition(): array
    {
        $fake = Faker::create();
        
        return [
            'id' => Utilisateur::factory()->create()->id,
            'poste' => $fake->jobTitle,
        ];
    }
}
