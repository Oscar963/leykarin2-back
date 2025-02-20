<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnexoRequest extends FormRequest
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
        $anexoId = $isUpdate ? $this->route('anexo') : null;

        return [
            'internal_number' => [
                'required',
                'numeric',
                $isUpdate ? 'unique:anexos,internal_number,' . $anexoId : 'unique:anexos,internal_number',
            ],
            'external_number' => [
                'required',
                'numeric',
                $isUpdate ? 'unique:anexos,external_number,' . $anexoId : 'unique:anexos,external_number',
            ],
            'office' => 'nullable|string|max:200',
            'unit' => 'nullable|string|max:200',
            'person' => 'nullable|string|max:200',
        ];
    }
}
