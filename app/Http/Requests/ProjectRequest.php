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
            'token_purchase_plan' => 'required|exists:purchase_plans,token',
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
            'token_purchase_plan.required' => 'El token del plan de compra es requerido',
            'token_purchase_plan.exists' => 'El token del plan de compra seleccionado no existe',
        ];
    }
}
