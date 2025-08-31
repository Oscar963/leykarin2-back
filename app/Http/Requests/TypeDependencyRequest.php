<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypeDependencyRequest extends FormRequest
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
            'code' => 'required|max:2|unique:type_dependencies,code',
            'email_notification' => 'required|email|max:255',
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
            'code' => 'Código',
            'email_notification' => 'Correo electrónico',
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
            'code.required' => 'El código es obligatorio.',
            'code.max' => 'El código debe tener un máximo de 2 caracteres.',
            'code.unique' => 'El código ya existe.',
            'email_notification.required' => 'El correo electrónico es obligatorio.',
            'email_notification.email' => 'El correo electrónico debe ser válido.',
            'email_notification.max' => 'El correo electrónico debe tener un máximo de 255 caracteres.',
        ];
    }
}
