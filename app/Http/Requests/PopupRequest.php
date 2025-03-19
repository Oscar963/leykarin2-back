<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PopupRequest extends FormRequest
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
        $bannerId = $isUpdate ? $this->route('banner') : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? 'unique:banners,title,' . $bannerId : 'unique:banners,title',
            ],
            'status' => 'required|in:published,hidden',
            'link' => 'nullable|url:http,https',
            'image' => $isUpdate ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'date_expiration' => 'required|date|after_or_equal:date',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'title.unique' => 'El título ya ha sido registrado, debe ser único.',
            'status.in' => 'El estado debe ser publicado o oculto.',
            'image.required' => 'La imagen es obligatoria.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, webp.',
            'image.max' => 'La imagen no puede exceder los 2048 kilobytes.',
            'date_expiration.required' => 'La fecha de expiración es obligatoria.',
            'date_expiration.date' => 'La fecha de expiración no es válida.',
            'date_expiration.after_or_equal' => 'La fecha de expiración debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
