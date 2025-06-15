<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'unit_purchasing_id' => 'required|exists:unit_purchasings,id',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'type_project_id' => 'required|exists:type_projects,id',
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
        ];
    }
}
