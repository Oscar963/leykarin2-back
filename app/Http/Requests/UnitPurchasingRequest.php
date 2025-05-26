<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitPurchasingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la unidad de compra es requerido',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
        ];
    }
} 