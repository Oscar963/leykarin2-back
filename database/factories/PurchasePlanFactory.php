<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\PurchasePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchasePlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchasePlan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'token' => Str::random(32),
            'year' => $this->faker->numberBetween(2020, 2030),
            'direction_id' => Direction::factory(),
            'created_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    /**
     * Indicate that the purchase plan is for a specific year.
     */
    public function forYear(int $year)
    {
        return $this->state(function (array $attributes) use ($year) {
            return [
                'year' => $year,
            ];
        });
    }

    /**
     * Indicate that the purchase plan is for a specific direction.
     */
    public function forDirection(int $directionId)
    {
        return $this->state(function (array $attributes) use ($directionId) {
            return [
                'direction_id' => $directionId,
            ];
        });
    }

    /**
     * Indicate that the purchase plan was created by a specific user.
     */
    public function createdBy(int $userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'created_by' => $userId,
            ];
        });
    }
} 