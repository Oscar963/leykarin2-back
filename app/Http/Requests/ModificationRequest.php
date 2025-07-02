<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModificationRequest extends FormRequest
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
        $rules = [
            'date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'status' => 'sometimes|string|in:active,inactive,pending,approved,rejected',
            'purchase_plan_id' => 'required|exists:purchase_plans,id'
        ];

        // Para actualizaciones, algunos campos pueden ser opcionales
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['purchase_plan_id'] = 'sometimes|exists:purchase_plans,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'reason.required' => 'El motivo es obligatorio',
            'reason.string' => 'El motivo debe ser texto',
            'reason.max' => 'El motivo no puede exceder los 1000 caracteres',
            'status.string' => 'El estado debe ser texto',
            'status.in' => 'El estado debe ser uno de los valores permitidos',
            'purchase_plan_id.required' => 'El plan de compra es obligatorio',
            'purchase_plan_id.exists' => 'El plan de compra seleccionado no existe'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'date' => 'fecha',
            'reason' => 'motivo',
            'status' => 'estado',
            'purchase_plan_id' => 'plan de compra'
        ];
    }
} 