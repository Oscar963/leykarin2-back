<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRules;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $userId = $isUpdate ? $this->route('user') : null;

        return [
            'name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'maternal_surname' => 'required|string|max:255',
            'rut' => [
                'required',
                'string',
                'max:20',
                $isUpdate ? Rule::unique('users')->ignore($userId) : 'unique:users',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $isUpdate ? Rule::unique('users')->ignore($userId) : 'unique:users',
            ],
            'status' => 'required|boolean',
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                PasswordRules::defaults(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'paternal_surname.required' => 'El apellido paternal es obligatorio.',
            'paternal_surname.max' => 'El apellido paternal no puede tener más de 255 caracteres.',
            'maternal_surname.required' => 'El apellido maternal es obligatorio.',
            'maternal_surname.max' => 'El apellido maternal no puede tener más de 255 caracteres.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'El RUT ya ha sido registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya ha sido registrado.',
            'status.required' => 'El estado es obligatorio.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'email_verified_at.date' => 'La fecha de verificación del correo debe ser una fecha válida.',
        ];
    }
}
