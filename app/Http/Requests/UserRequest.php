<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Helpers\RutHelper;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
        $userId = $this->route('user');

        $rules = [
            'name' => 'required|max:255',
            'paternal_surname' => 'required|max:255',
            'maternal_surname' => 'required|max:255',
            'rut' => [
                'required',
                'max:255',
                Rule::unique('users', 'rut')->ignore($userId),
            ],
            'email' => [
                'required',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'status' => 'required|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string',
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        } else {
            $rules['password'] = ['nullable', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }

        return $rules;
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
            'name.max' => 'El nombre debe tener un máximo de 255 caracteres.',
            'paternal_surname.required' => 'El apellido paterno es obligatorio.',
            'paternal_surname.max' => 'El apellido paterno debe tener un máximo de 255 caracteres.',
            'maternal_surname.required' => 'El apellido materno es obligatorio.',
            'maternal_surname.max' => 'El apellido materno debe tener un máximo de 255 caracteres.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'El RUT ya ha sido registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya ha sido registrado.',
            'status.required' => 'El estado es obligatorio.',
            'status.max' => 'El estado debe tener un máximo de 255 caracteres.',
            'roles.required' => 'El rol es obligatorio.',
            'roles.array' => 'El rol debe ser un array.',
            'roles.min' => 'El rol debe tener al menos 1 elemento.',
            'roles.*.string' => 'El rol debe ser una cadena de caracteres.',
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
            'paternal_surname' => 'Apellido Paterno',
            'maternal_surname' => 'Apellido Materno',
            'rut' => 'RUT',
            'email' => 'Correo Electrónico',
            'status' => 'Estado',
            'roles' => 'Rol',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('rut')) {
            $this->merge([
                'rut' => RutHelper::normalize($this->input('rut')),
            ]);
        }
    }
}
