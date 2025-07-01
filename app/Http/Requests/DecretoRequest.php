<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecretoRequest extends FormRequest
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
        return [
            'file' => 'required|file|mimes:pdf|max:5120', // Máximo 5MB (5120 KB)
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo del decreto es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un PDF',
            'file.max' => 'El archivo no debe superar los 5MB',
            'purchase_plan_id.required' => 'El plan de compra es requerido',
            'purchase_plan_id.exists' => 'El plan de compra no existe',
        ];
    }
} 