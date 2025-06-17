<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $itemPurchaseId = $isUpdate ? $this->route('item_purchase') : null;

        return [
            'product_service' => 'required|string|max:255',
            'quantity_item' => 'required|numeric|min:1',
            'amount_item' => 'required|numeric|min:0',
            'quantity_oc' => 'required|numeric|min:1',
            'months_oc' => 'required|string',
            'regional_distribution' => 'required|string',
            'cod_budget_allocation_type' => 'required|string',
            'budget_allocation_id' => 'required|exists:budget_allocations,id',
            'type_purchase_id' => 'required|exists:type_purchases,id',
            'project_id' => 'required|exists:projects,id', 

        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del ítem es requerido',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'description.required' => 'La descripción es requerida',
            'quantity.required' => 'La cantidad es requerida',
            'quantity.numeric' => 'La cantidad debe ser un número',
            'quantity.min' => 'La cantidad debe ser mayor a 0',
            'unit_price.required' => 'El precio unitario es requerido',
            'unit_price.numeric' => 'El precio unitario debe ser un número',
            'unit_price.min' => 'El precio unitario debe ser mayor o igual a 0',
            'total_price.required' => 'El precio total es requerido',
            'total_price.numeric' => 'El precio total debe ser un número',
            'total_price.min' => 'El precio total debe ser mayor o igual a 0',
            'status_id.required' => 'El estado es requerido',
            'status_id.exists' => 'El estado seleccionado no existe',
            'project_id.required' => 'El proyecto es requerido',
            'project_id.exists' => 'El proyecto seleccionado no existe',
        ];
    }
}
