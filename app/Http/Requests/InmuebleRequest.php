<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InmuebleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'numero' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'calle' => 'required|string|max:255',
        ];

        // Add unique validation for numero when creating
        if ($this->isMethod('POST')) {
            $rules['numero'] .= '|unique:inmuebles,numero';
        } else {
            $rules['numero'] .= '|unique:inmuebles,numero,' . $this->inmueble->id;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero.required' => 'El número del inmueble es obligatorio.',
            'numero.string' => 'El número debe ser una cadena de texto.',
            'numero.max' => 'El número no puede tener más de 255 caracteres.',
            'numero.unique' => 'Ya existe un inmueble con este número.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'calle.required' => 'La calle es obligatoria.',
            'calle.string' => 'La calle debe ser una cadena de texto.',
            'calle.max' => 'La calle no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numero' => 'número del inmueble',
            'descripcion' => 'descripción',
            'calle' => 'calle',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Los datos proporcionados no son válidos.',
                    'errors' => $validator->errors()
                ],
                'timestamp' => now()->toISOString()
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string fields
        $this->merge([
            'numero' => trim($this->numero),
            'descripcion' => trim($this->descripcion),
            'calle' => trim($this->calle),
        ]);
    }
} 