<?php
namespace Database\Factories;

use App\Models\Marquer;
use App\Models\Employer;
use App\Models\Presence;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class MarquerFactory extends Factory
{
    protected $model = Marquer::class;

    public function definition()
    {
        $faker = Faker::create();

        return [
            'Empl_id' => Employer::factory()->create()->id,
            'id' => Presence::factory()->create()->id,
        ];
    }
}
