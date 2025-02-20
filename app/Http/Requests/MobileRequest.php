<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileRequest extends FormRequest
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
        $movileId = $isUpdate ? $this->route('mobile') : null;

        return [
            'number' => [
                'required',
                'numeric',
                $isUpdate ? 'unique:mobiles,number,' . $movileId : 'unique:mobiles,number',
            ],
            'office' => 'nullable|string|max:200',
            'direction' => 'nullable|string|max:200',
            'person' => 'nullable|string|max:200',
        ];
    }
}
