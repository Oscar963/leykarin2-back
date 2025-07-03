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
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'version' => 'required|string|max:50',
            'date' => 'required|date|before_or_equal:today',
            'modification_type_id' => 'required|exists:modification_types,id',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'status' => 'sometimes|string|in:active,inactive,pending,approved,rejected'
        ];

        // Para actualizaciones, algunos campos pueden ser opcionales
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string|max:2000';
            $rules['version'] = 'sometimes|string|max:50';
            $rules['date'] = 'sometimes|date|before_or_equal:today';
            $rules['modification_type_id'] = 'sometimes|exists:modification_types,id';
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
            'name.required' => 'El nombre es obligatorio',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'description.required' => 'La descripción es obligatoria',
            'description.string' => 'La descripción debe ser texto',
            'description.max' => 'La descripción no puede exceder los 2000 caracteres',
            'version.required' => 'La versión es obligatoria',
            'version.string' => 'La versión debe ser texto',
            'version.max' => 'La versión no puede exceder los 50 caracteres',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'date.before_or_equal' => 'La fecha no puede ser futura',
            'modification_type_id.required' => 'El tipo de modificación es obligatorio',
            'modification_type_id.exists' => 'El tipo de modificación seleccionado no existe',
            'purchase_plan_id.required' => 'El plan de compra es obligatorio',
            'purchase_plan_id.exists' => 'El plan de compra seleccionado no existe',
            'status.string' => 'El estado debe ser texto',
            'status.in' => 'El estado debe ser uno de los valores permitidos'
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
            'name' => 'nombre',
            'description' => 'descripción',
            'version' => 'versión',
            'date' => 'fecha',
            'modification_type_id' => 'tipo de modificación',
            'purchase_plan_id' => 'plan de compra',
            'status' => 'estado'
        ];
    }
} 