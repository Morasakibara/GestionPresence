<?php

namespace Database\Factories;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\WorkplaceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PresenceFactory extends Factory
{
    protected $model = Presence::class;

    public function definition()
    {
        $date = $this->faker->dateTimeThisMonth();
        $heureArrivee = (new Carbon($date))->setTime(rand(7, 9), rand(0, 59), rand(0, 59));
        $heureDepart = (new Carbon($date))->setTime(rand(17, 18), rand(0, 59), rand(0, 59));

        $status = $this->faker->randomElement(['présent', 'Absent']);

        $workplace = WorkplaceLocation::inRandomOrder()->first();
        if (!$workplace) {
            $workplace = WorkplaceLocation::factory()->create();
        }

        $latitude = $this->faker->latitude();
        $longitude = $this->faker->longitude();

        return [
            'Sup_id' => function () {
                return Superviseur::factory()->create()->id;
            },
            'employerID' => function () {
                return Employer::factory()->create()->id;
            },
            'heureArrivee' => $heureArrivee,
            'heureDepart' => $status === 'présent' ? $heureDepart : null,
            'date' => $date,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
            'latitude_arrivee' => $latitude,
            'longitude_arrivee' => $longitude,
            'latitude_depart' => $status === 'présent' ? $latitude : null,
            'longitude_depart' => $status === 'présent' ? $longitude : null,
            'localisation_validee_arrivee' => $this->faker->boolean(),
            'localisation_validee_depart' => $status === 'présent' ? $this->faker->boolean() : false,
            'workplace_location_id' => $workplace->id,
        ];
    }
}
