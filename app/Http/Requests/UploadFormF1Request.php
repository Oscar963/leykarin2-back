<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFormF1Request extends FormRequest
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
            'description' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:xls,xlsx',
            'token_purchase_plan' => 'required|exists:purchase_plans,token',
            'amount_F1' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'description.string' => 'La descripción debe ser un texto',
            'description.max' => 'La descripción debe tener máximo 255 caracteres',
            'file.required' => 'El archivo es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un archivo XLS o XLSX',
            'token_purchase_plan.required' => 'El token del plan de compra es requerido',
            'token_purchase_plan.exists' => 'El token del plan de compra no existe',
            'amount_F1.required' => 'El monto es requerido',
            'amount_F1.numeric' => 'El monto debe ser un número',
            'amount_F1.min' => 'El monto debe ser mayor o igual a 0',
        ];
    }
}
