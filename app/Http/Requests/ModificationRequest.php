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
            'modification_type_id' => 'required|exists:modification_types,id',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'status' => 'sometimes|string|in:active,inactive,pending,approved,rejected',
            'email_content' => 'sometimes|string|max:5000'
        ];

        // Para actualizaciones, algunos campos pueden ser opcionales
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string|max:2000';    
            $rules['modification_type_id'] = 'sometimes|exists:modification_types,id';
            $rules['purchase_plan_id'] = 'sometimes|exists:purchase_plans,id';
            $rules['email_content'] = 'sometimes|string|max:5000';
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
            'modification_type_id.required' => 'El tipo de modificación es obligatorio',
            'modification_type_id.exists' => 'El tipo de modificación seleccionado no existe',
            'purchase_plan_id.required' => 'El plan de compra es obligatorio',
            'purchase_plan_id.exists' => 'El plan de compra seleccionado no existe',
            'status.string' => 'El estado debe ser texto',
            'status.in' => 'El estado debe ser uno de los valores permitidos',
            'email_content.string' => 'El contenido del correo debe ser texto',
            'email_content.max' => 'El contenido del correo no puede exceder los 5000 caracteres'
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
            'modification_type_id' => 'tipo de modificación',
            'purchase_plan_id' => 'plan de compra',
            'status' => 'estado',
            'email_content' => 'contenido del correo'
        ];
    }
} 