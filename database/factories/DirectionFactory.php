<?php

namespace Database\Factories;

use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

class DirectionFactory extends Factory
{
    protected $model = Direction::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'status' => true,
            'director_id' => null,
        ];
    }
} 