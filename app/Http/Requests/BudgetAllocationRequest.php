<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetAllocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $budgetAllocationId = $isUpdate ? $this->route('budget_allocation') : null;

        return [
            'description' => [
                'required',
                'string',
                'max:100',
                $isUpdate ? 'unique:budget_allocations,description,' . $budgetAllocationId : 'unique:budget_allocations,description',
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                $isUpdate ? 'unique:budget_allocations,code,' . $budgetAllocationId : 'unique:budget_allocations,code',
            ],
            'cod_budget_allocation_type' => [
                'required',
                'string',
                'max:20',
            ],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'La descripción de la asignación presupuestaria es obligatoria.',
            'description.string' => 'La descripción de la asignación presupuestaria debe ser una cadena de texto.',
            'description.max' => 'La descripción de la asignación presupuestaria no debe exceder los 100 caracteres.',
            'description.unique' => 'Esta descripción de asignación presupuestaria ya está registrada.',
            'code.required' => 'El código de la asignación presupuestaria es obligatorio.',
            'code.string' => 'El código de la asignación presupuestaria debe ser una cadena de texto.',
            'code.max' => 'El código de la asignación presupuestaria no debe exceder los 20 caracteres.',
            'code.unique' => 'Este código de asignación presupuestaria ya está registrado.',
            'cod_budget_allocation_type.required' => 'El tipo de asignación presupuestaria es obligatorio.',
            'cod_budget_allocation_type.string' => 'El tipo de asignación presupuestaria debe ser una cadena de texto.',
            'cod_budget_allocation_type.max' => 'El tipo de asignación presupuestaria no debe exceder los 20 caracteres.',
        ];
    }
} 