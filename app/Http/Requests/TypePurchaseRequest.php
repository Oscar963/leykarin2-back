<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypePurchaseRequest extends FormRequest
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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $typePurchaseId = $isUpdate ? $this->route('type_purchase') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                $isUpdate ? 'unique:type_purchases,name,' . $typePurchaseId : 'unique:type_purchases,name',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del tipo de compra es obligatorio.',
            'name.string' => 'El nombre del tipo de compra debe ser una cadena de texto.',
            'name.max' => 'El nombre del tipo de compra no debe exceder los 100 caracteres.',
            'name.unique' => 'Este nombre de tipo de compra ya está registrado.',
        ];
    }
}
