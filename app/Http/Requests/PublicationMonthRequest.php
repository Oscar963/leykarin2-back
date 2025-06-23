<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicationMonthRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $publicationMonthId = $isUpdate ? $this->route('publication_month') : null;

        return [
            'name' => 'required|string|max:50',
            'short_name' => 'required|string|max:10',
            'month_number' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del mes es requerido',
            'name.max' => 'El nombre no puede tener más de 50 caracteres',
            'short_name.required' => 'El nombre corto del mes es requerido',
            'short_name.max' => 'El nombre corto no puede tener más de 10 caracteres',
            'month_number.required' => 'El número del mes es requerido',
            'month_number.integer' => 'El número del mes debe ser un número entero',
            'month_number.min' => 'El número del mes debe ser mayor o igual a 1',
            'month_number.max' => 'El número del mes debe ser menor o igual a 12',
            'year.required' => 'El año es requerido',
            'year.integer' => 'El año debe ser un número entero',
            'year.min' => 'El año debe ser mayor o igual a 2020',
            'year.max' => 'El año debe ser menor o igual a 2030',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation()
    {
        // Convertir el nombre y nombre corto a formato de título
        if ($this->has('name')) {
            $this->merge([
                'name' => ucfirst(strtolower(trim($this->name)))
            ]);
        }

        if ($this->has('short_name')) {
            $this->merge([
                'short_name' => ucfirst(strtolower(trim($this->short_name)))
            ]);
        }
    }
} 