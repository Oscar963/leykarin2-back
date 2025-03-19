<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $pageId = $isUpdate ? $this->route('page') : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                // Validación de título único
                $isUpdate ? 'unique:pages,title,' . $pageId : 'unique:pages,title',
            ],
            'content' => 'nullable|string',
            'status' => 'required|in:published,hidden',
            'image' => $isUpdate ? 'image|mimes:jpeg,png,jpg,gif,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'title.unique' => 'El título ya ha sido registrado, debe ser único.',
            'status.in' => 'El status debe ser publicado o oculto.',
            'image.image' => 'El archivo debe ser una image.',
            'image.mimes' => 'La image debe ser de tipo: jpeg, png, jpg, gif, webp.',
            'image.max' => 'La image no puede exceder los 2048 kilobytes.',
        ];
    }
}
