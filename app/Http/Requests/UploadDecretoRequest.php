<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDecretoRequest extends FormRequest
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
            'token_purchase_plan' => 'required|exists:purchase_plans,token',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo del decreto es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un PDF',
            'file.max' => 'El archivo no debe superar los 5MB',
            'token_purchase_plan.required' => 'El token del plan de compra es requerido',
            'token_purchase_plan.exists' => 'El token del plan de compra no existe',
        ];
    }
} 