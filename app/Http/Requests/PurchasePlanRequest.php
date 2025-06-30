<?php

namespace App\Http\Requests;

use App\Rules\UniqueDirectionYearPlan;
use Illuminate\Foundation\Http\FormRequest;

class PurchasePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('patch');
        $excludeId = $isUpdate ? $this->route('purchase_plan') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'year' => [
                'required',
                'integer',
                new UniqueDirectionYearPlan($excludeId),
            ],
            'direction_id' => [
                'required',
                'exists:directions,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del plan de compra es obligatorio.',
            'name.string' => 'El nombre del plan de compra debe ser una cadena de caracteres.',
            'name.max' => 'El nombre del plan de compra debe tener máximo 255 caracteres.',

            'year.required' => 'El año del plan de compra es obligatorio.',
            'year.integer' => 'El año del plan de compra debe ser un número entero.',

            'direction_id.required' => 'La dirección es obligatoria.',
            'direction_id.exists' => 'La dirección seleccionada no es válida.',
        ];
    }
}
