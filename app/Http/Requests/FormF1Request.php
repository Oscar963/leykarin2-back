<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormF1Request extends FormRequest
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
            'file' => 'required|file|mimes:xls,xlsx',
            'purchase_plan_id' => 'required|exists:purchase_plans,id',
            'amount' => 'required|numeric|min:0|max:9999999999999.99|regex:/^\d{1,13}(\.\d{1,2})?$/',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo es requerido',
            'file.file' => 'El archivo debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser un archivo XLS o XLSX',
            'purchase_plan_id.required' => 'El plan de compra es requerido',
            'purchase_plan_id.exists' => 'El plan de compra no existe',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor o igual a 0',
            'amount.max' => 'El monto debe ser menor o igual a 9999999999999.99',
            'amount.regex' => 'El monto debe tener máximo 2 decimales',
        ];
    }
}
