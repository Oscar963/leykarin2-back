<?php

namespace App\Rules;

use App\Models\PurchasePlan;
use Illuminate\Contracts\Validation\Rule;

class UniqueDirectionYearPlan implements Rule
{
    protected $excludeId;

    /**
     * Create a new rule instance.
     *
     * @param int|null $excludeId - ID del plan a excluir (útil para validaciones en actualizaciones)
     * @return void
     */
    public function __construct(?int $excludeId = null)
    {
        $this->excludeId = $excludeId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $directionId = request()->input('direction');
        $year = request()->input('year');

        if (!$directionId || !$year) {
            return true; // Si no hay dirección o año, no podemos validar
        }

        return !PurchasePlan::existsForDirectionAndYear($directionId, $year, $this->excludeId);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $directionId = request()->input('direction');
        $year = request()->input('year');

        // Obtener el nombre de la dirección para el mensaje
        $directionName = 'la dirección especificada';
        if ($directionId) {
            $direction = \App\Models\Direction::find($directionId);
            if ($direction) {
                $directionName = $direction->name;
            }
        }

        return "Ya existe un plan de compras para {$directionName} en el año {$year}. No se puede crear otro plan para la misma dirección y año.";
    }
}
