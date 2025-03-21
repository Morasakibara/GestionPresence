<?php

namespace Database\Factories;

use App\Models\WorkplaceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkplaceLocationFactory extends Factory
{
    protected $model = WorkplaceLocation::class;

    public function definition()
    {
        return [
            'nom' => $this->faker->company(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'rayon' => $this->faker->numberBetween(50, 500),
            'actif' => true,
        ];
    }
}
