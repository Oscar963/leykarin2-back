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
            'numero' => 'required|max:255',
            'descripcion' => 'required|max:1000',
            'calle' => 'required|max:255',
            'numeracion' => 'required|max:255',
            'lote_sitio' => 'nullable|max:255',
            'manzana' => 'nullable|max:255',
            'poblacion_villa' => 'required|max:255',
            'foja' => 'required|max:255',
            'inscripcion_numero' => 'required|max:255',
            'inscripcion_anio' => 'required|max:255',
            'rol_avaluo' => 'nullable|max:255',
            'superficie' => 'nullable|max:255',
            'deslinde_norte' => 'nullable|max:255',
            'deslinde_sur' => 'nullable|max:255',
            'deslinde_este' => 'nullable|max:255',
            'deslinde_oeste' => 'nullable|max:255',
            'decreto_incorporacion' => 'nullable|max:255',
            'decreto_destinacion' => 'nullable|max:255',
            'observaciones' => 'nullable|max:1000',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
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
