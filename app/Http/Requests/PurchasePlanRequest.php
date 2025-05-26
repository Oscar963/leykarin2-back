<?php

namespace App\Http\Requests;

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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $purchasePlanId = $isUpdate ? $this->route('purchase_plan') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'year' => [
                'required',
                'integer',
            ]
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'La fecha del plan de compra es obligatoria.',
            'date.date' => 'La fecha del plan de compra debe ser una fecha válida.',

            'sending_date.date' => 'La fecha de envío debe ser una fecha válida.',
            'sending_date.after_or_equal' => 'La fecha de envío debe ser igual o posterior a la fecha del plan.',

            'modification_date.date' => 'La fecha de modificación debe ser una fecha válida.',
            'modification_date.after_or_equal' => 'La fecha de modificación debe ser igual o posterior a la fecha del plan.',

            'status_id.required' => 'El estado del plan de compra es obligatorio.',
            'status_id.exists' => 'El estado seleccionado no es válido.',
        ];
    }
}
