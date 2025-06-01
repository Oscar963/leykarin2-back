<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePlanRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('patch');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'year' => [
                'required',
                'integer',
            ],
            'amount_F1' => [
                'required',
                'numeric',
                'min:0',
            ],
            'name_file' => [
                'required',
                'string',
                'max:255',
            ],
            'description_file' => [
                'nullable',
                'string',
                'max:255',
            ],
            'file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:xls,xlsx',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del plan de compra es obligatorio.',
            'name.string' => 'El nombre del plan de compra debe ser una cadena de caracteres.',
            'name.max' => 'El nombre del plan de compra debe tener máximo 255 caracteres.',

            'year.required' => 'El año del plan de compra es obligatorio.',
            'year.integer' => 'El año del plan de compra debe ser un número entero.',

            'amount_F1.required' => 'El monto del plan de compra es obligatorio.',
            'amount_F1.numeric' => 'El monto del plan de compra debe ser un número.',
            'amount_F1.min' => 'El monto del plan de compra debe ser mayor o igual a 0.',

            'name_file.required' => 'El nombre del archivo es obligatorio.',
            'name_file.string' => 'El nombre del archivo debe ser una cadena de caracteres.',
            'name_file.max' => 'El nombre del archivo debe tener máximo 255 caracteres.',

            'description_file.string' => 'La descripción del archivo debe ser una cadena de caracteres.',
            'description_file.max' => 'La descripción del archivo debe tener máximo 255 caracteres.',

            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser un archivo de tipo: xls, xlsx.',
        ];
    }
}
