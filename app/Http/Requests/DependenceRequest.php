<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DependenceRequest extends FormRequest
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
        $dependenceId = $isUpdate ? $this->route('dependence') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                $isUpdate ? 'unique:dependences,name,' . $dependenceId : 'unique:dependences,name',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre de la dependencia es obligatorio.',
            'name.string' => 'El nombre de la dependencia debe ser una cadena de texto.',
            'name.max' => 'El nombre de la dependencia no debe exceder los 100 caracteres.',
            'name.unique' => 'Este nombre de dependencia ya está registrado.',
        ];
    }
} 