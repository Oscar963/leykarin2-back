<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;


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
        $rules = [
            'name' => 'required|max:255',
            'paternal_surname' => 'required|max:255',
            'maternal_surname' => 'required|max:255',
            'rut' => 'required|max:255',
            'email' => 'required|max:255',
            'status' => 'required|max:255',
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        } else {
            $rules['password'] = ['nullable', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }

        return $rules;
    }
}
