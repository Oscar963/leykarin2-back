<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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

        return [
            'file' => [
                'file',
                'max:250000', //250MB
                $isUpdate ? 'nullable' : 'required',
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'page_id' => 'nullable|integer|exists:pages,id',
        ];
    }
}
