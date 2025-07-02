<?php

namespace Database\Factories;

use App\Models\Modification;
use App\Models\PurchasePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Modification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $purchasePlan = PurchasePlan::inRandomOrder()->first() ?? PurchasePlan::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        
        return [
            'modification_number' => Modification::getNextModificationNumber($purchasePlan->id),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'reason' => $this->faker->paragraph(2),
            'status' => $this->faker->randomElement([
                Modification::STATUS_ACTIVE,
                Modification::STATUS_INACTIVE,
                Modification::STATUS_PENDING,
                Modification::STATUS_APPROVED,
                Modification::STATUS_REJECTED
            ]),
            'purchase_plan_id' => $purchasePlan->id,
            'created_by' => $user->id,
            'updated_by' => $this->faker->optional(0.3)->randomElement([$user->id, null]),
        ];
    }

    /**
     * Indica que la modificación está activa
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Modification::STATUS_ACTIVE,
            ];
        });
    }

    /**
     * Indica que la modificación está pendiente
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Modification::STATUS_PENDING,
            ];
        });
    }

    /**
     * Indica que la modificación está aprobada
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Modification::STATUS_APPROVED,
            ];
        });
    }

    /**
     * Indica que la modificación está rechazada
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Modification::STATUS_REJECTED,
            ];
        });
    }

    /**
     * Indica que la modificación está inactiva
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Modification::STATUS_INACTIVE,
            ];
        });
    }

    /**
     * Para un plan de compra específico
     */
    public function forPurchasePlan(PurchasePlan $purchasePlan)
    {
        return $this->state(function (array $attributes) use ($purchasePlan) {
            return [
                'purchase_plan_id' => $purchasePlan->id,
                'modification_number' => Modification::getNextModificationNumber($purchasePlan->id),
            ];
        });
    }

    /**
     * Creada por un usuario específico
     */
    public function createdBy(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'created_by' => $user->id,
            ];
        });
    }
} 