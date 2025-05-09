<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypeComplaintRequest extends FormRequest
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
        $typecomplaintId = $isUpdate ? $this->route('type_complaint') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                $isUpdate ? 'unique:type_complaints,name,' . $typecomplaintId : 'unique:type_complaints,name',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del tipo de denuncia es obligatorio.',
            'name.string' => 'El nombre del tipo de denuncia debe ser una cadena de texto.',
            'name.max' => 'El nombre del tipo de denuncia no debe exceder los 100 caracteres.',
            'name.unique' => 'Este nombre de tipo de denuncia ya está registrado.',
        ];
    }
}
