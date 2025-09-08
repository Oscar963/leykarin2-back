<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WitnessRequest extends FormRequest
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
        return [
            'name' => 'required|max:255',
            'phone' => 'required|max:255',
            'email' => 'required|email|max:255',
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
            'phone' => 'Teléfono',
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
            'name.max' => 'El nombre debe tener un máximo de 255 caracteres.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.max' => 'El teléfono debe tener un máximo de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico debe tener un máximo de 255 caracteres.',
        ];
    }
}
