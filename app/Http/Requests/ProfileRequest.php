<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $userId = $this->user()->id; // Obtiene el ID del usuario autenticado de forma más directa

        return [
            'name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'maternal_surname' => 'required|string|max:255',
            'rut' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($userId), // Ignora el ID del usuario autenticado
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
        ];
    }

    /**
     * Get the custom attributes for the defined validation rules.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nombre',
            'paternal_surname' => 'Apellido paterno',
            'maternal_surname' => 'Apellido materno',
            'rut' => 'RUT',
            'email' => 'Correo electrónico',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [    
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'paternal_surname.required' => 'El apellido paterno es obligatorio.',
            'paternal_surname.max' => 'El apellido paterno no puede tener más de 255 caracteres.',
            'maternal_surname.required' => 'El apellido materno es obligatorio.',
            'maternal_surname.max' => 'El apellido materno no puede tener más de 255 caracteres.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'El RUT ya ha sido registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya ha sido registrado.',
        ];
    }
}
