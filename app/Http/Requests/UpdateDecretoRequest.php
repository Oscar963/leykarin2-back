<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDecretoRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo del decreto es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un PDF',
            'file.max' => 'El archivo no debe superar los 5MB',
        ];
    }
} 