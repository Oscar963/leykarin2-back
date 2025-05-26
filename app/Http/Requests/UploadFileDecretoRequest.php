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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser un texto.',
            'name.max' => 'El nombre debe tener máximo 255 caracteres.',
            'description.string' => 'La descripción debe ser un texto.',
            'description.max' => 'La descripción debe tener máximo 255 caracteres.',
            'file.required' => 'El archivo es requerido.',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser un archivo válido.',
            'purchase_plan_id.required' => 'El plan de compra es requerido.',
            'purchase_plan_id.exists' => 'El plan de compra no existe.',
        ];
    }
}
