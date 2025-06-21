<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DirectionRequest extends FormRequest
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
        $directionId = $isUpdate ? $this->route('direction') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? 'unique:directions,name,' . $directionId : 'unique:directions,name',
            ],
            'alias' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? 'unique:directions,alias,' . $directionId : 'unique:directions,alias',
            ],
            'director_id' => [
                'required',
                'exists:users,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre de la dirección es obligatorio.',
            'name.string' => 'El nombre de la dirección debe ser una cadena de texto.',
            'name.max' => 'El nombre de la dirección no debe exceder los 255 caracteres.',
            'name.unique' => 'Este nombre de dirección ya está registrado.',
            'alias.required' => 'El alias de la dirección es obligatorio.',
            'alias.string' => 'El alias de la dirección debe ser una cadena de texto.',
            'alias.max' => 'El alias de la dirección no debe exceder los 255 caracteres.',
            'alias.unique' => 'Este alias de dirección ya está registrado.',
            'director_id.required' => 'El director es obligatorio.',
            'director_id.exists' => 'El director seleccionado no existe.',
        ];
    }
} 