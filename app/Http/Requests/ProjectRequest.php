<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos para la validación
     */
    protected function prepareForValidation()
    {
        // Si goals viene como string JSON, decodificarlo a array
        if ($this->has('goals') && is_string($this->goals)) {
            $decodedGoals = json_decode($this->goals, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedGoals)) {
                $this->merge([
                    'goals' => $decodedGoals
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'unit_purchasing_id' => 'required|exists:unit_purchasings,id',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'type_project_id' => 'required|exists:type_projects,id',

            // Validaciones para las metas (solo para proyectos estratégicos)
            'goals' => 'sometimes|array',
            'goals.*.name' => 'required|string|max:255',
            'goals.*.description' => 'nullable|string',
            'goals.*.target_value' => 'nullable|numeric|min:0',
            'goals.*.progress_value' => 'nullable|numeric|min:0',
            'goals.*.unit_measure' => 'nullable|string|max:100',
            'goals.*.target_date' => 'nullable|date',
            'goals.*.notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proyecto es requerido',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'description.required' => 'La descripción del proyecto es requerida',
            'unit_purchasing_id.required' => 'La unidad de compra es requerida',
            'unit_purchasing_id.exists' => 'La unidad de compra seleccionada no existe',
            'purchase_plan_id.required' => 'El plan de compra es requerido',
            'purchase_plan_id.exists' => 'El plan de compra seleccionado no existe',
            'type_project_id.required' => 'El tipo de proyecto es requerido',
            'type_project_id.exists' => 'El tipo de proyecto seleccionado no existe',

            // Mensajes para las metas
            'goals.array' => 'Las metas deben ser un arreglo válido',
            'goals.*.name.required' => 'El nombre de la meta es requerido',
            'goals.*.name.max' => 'El nombre de la meta no puede tener más de 255 caracteres',
            'goals.*.description.nullable' => 'La descripción de la meta puede ser nula',
            'goals.*.target_value.numeric' => 'El valor objetivo debe ser un número',
            'goals.*.target_value.min' => 'El valor objetivo debe ser mayor o igual a 0',
            'goals.*.progress_value.numeric' => 'El valor de progreso debe ser un número',
            'goals.*.progress_value.min' => 'El valor de progreso debe ser mayor o igual a 0',
            'goals.*.unit_measure.max' => 'La unidad de medida no puede tener más de 100 caracteres',
            'goals.*.target_date.date' => 'La fecha objetivo debe ser una fecha válida',
        ];
    }
}
