<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileDecretoRequest extends FormRequest
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
            'name_file' => 'required|string|max:255',
            'description_file' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf',
            'token_purchase_plan' => 'required|exists:purchase_plans,token',
        ];
    }

    public function messages()
    {
        return [
            'name_file.required' => 'El nombre es requerido.',
            'name_file.string' => 'El nombre debe ser un texto.',
            'name_file.max' => 'El nombre debe tener máximo 255 caracteres.',
            'description_file.string' => 'La descripción debe ser un texto.',
            'description_file.max' => 'La descripción debe tener máximo 255 caracteres.',
            'file.required' => 'El archivo es requerido.',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser un archivo válido.',
            'token_purchase_plan.required' => 'El token del plan de compra es requerido.',
            'token_purchase_plan.exists' => 'El token del plan de compra no existe.',
        ];
    }
}
