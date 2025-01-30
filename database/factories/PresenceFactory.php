<?php

namespace Database\Factories;

use App\Models\Employer;
use App\Models\Superviseur;
use App\Models\Presence;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class PresenceFactory extends Factory
{
    protected $model = Presence::class;

    public function definition()
    {
        $faker = Faker::create();

        return [
            'Sup_id' => Superviseur::factory()->create()->id,
            'employerID' => Employer::factory()->create()->id,
            'heureArrivee' => $faker->dateTimeBetween('08:00:00', '10:00:00'),
            'heureDepart' => $faker->dateTimeBetween('16:00:00', '18:00:00'),
            'date' => $faker->date(),
            'status' => $faker->randomElement('present','absent'),
        ];
    }
}
