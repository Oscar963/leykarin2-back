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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:xls,xlsx',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'amount_F1' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser un texto',
            'name.max' => 'El nombre debe tener máximo 255 caracteres',
            'description.string' => 'La descripción debe ser un texto',
            'description.max' => 'La descripción debe tener máximo 255 caracteres',
            'file.required' => 'El archivo es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un archivo XLS o XLSX',
            'purchase_plan_id.required' => 'El plan de compra es requerido',
            'purchase_plan_id.exists' => 'El plan de compra no existe',
            'amount_F1.required' => 'El monto es requerido',
            'amount_F1.numeric' => 'El monto debe ser un número',
            'amount_F1.min' => 'El monto debe ser mayor o igual a 0',
        ];
    }
}
