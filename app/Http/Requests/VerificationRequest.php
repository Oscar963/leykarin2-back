<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
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
        return [
            'project_id' => 'required|exists:projects,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,bmp,webp',
        ];
    }

    public function messages()
    {
        return [
            'project_id.required' => 'El proyecto es requerido',
            'project_id.exists' => 'El proyecto no existe',
            'file.required' => 'El archivo es requerido',
            'file.mimes' => 'El archivo debe ser de tipo: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif, bmp, webp',
        ];
    }
}
