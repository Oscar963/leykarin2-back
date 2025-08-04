<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InmuebleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'numero' => 'required|max:255',
            'descripcion' => 'required|max:1000',
            'calle' => 'required|max:255',
            'numeracion' => 'required|max:255',
            'lote_sitio' => 'nullable|max:255',
            'manzana' => 'nullable|max:255',
            'poblacion_villa' => 'required|max:255',
            'foja' => 'required|max:255',
            'inscripcion_numero' => 'required|max:255',
            'inscripcion_anio' => 'required|max:255',
            'rol_avaluo' => 'nullable|max:255',
            'superficie' => 'nullable|max:255',
            'deslinde_norte' => 'nullable|max:255',
            'deslinde_sur' => 'nullable|max:255',
            'deslinde_este' => 'nullable|max:255',
            'deslinde_oeste' => 'nullable|max:255',
            'decreto_incorporacion' => 'nullable|max:255',
            'decreto_destinacion' => 'nullable|max:255',
            'observaciones' => 'nullable|max:1000',
        ];
    }

    /**
     * Get the custom attributes for the defined validation rules.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'numero' => 'Número',
            'descripcion' => 'Descripción',
            'calle' => 'Calle',
            'numeracion' => 'Numeración',
            'lote_sitio' => 'Lote/Sitio',
            'manzana' => 'Manzana',
            'poblacion_villa' => 'Población/Villa',
            'foja' => 'Foja',
            'inscripcion_numero' => 'Número de inscripción',
            'inscripcion_anio' => 'Año de inscripción',
            'rol_avaluo' => 'Rol de avaluo',
            'superficie' => 'Superficie',
            'deslinde_norte' => 'Deslinde Norte',
            'deslinde_sur' => 'Deslinde Sur',
            'deslinde_este' => 'Deslinde Este',
            'deslinde_oeste' => 'Deslinde Oeste',
            'decreto_incorporacion' => 'Decreto de incorporación',
            'decreto_destinacion' => 'Decreto de destinación',
            'observaciones' => 'Observaciones',
        ];
    }   

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'numero.required' => 'El número es obligatorio.',
            'numero.max' => 'El número debe tener un máximo de 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción debe tener un máximo de 1000 caracteres.',
            'calle.required' => 'La calle es obligatoria.',
            'calle.max' => 'La calle debe tener un máximo de 255 caracteres.',
            'numeracion.required' => 'La numeración es obligatoria.',
            'numeracion.max' => 'La numeración debe tener un máximo de 255 caracteres.',
            'lote_sitio.max' => 'El lote/sitio debe tener un máximo de 255 caracteres.',
            'manzana.max' => 'La manzana debe tener un máximo de 255 caracteres.',
            'poblacion_villa.required' => 'La población/villa es obligatoria.',
            'poblacion_villa.max' => 'La población/villa debe tener un máximo de 255 caracteres.',
            'foja.required' => 'La foja es obligatoria.',
            'foja.max' => 'La foja debe tener un máximo de 255 caracteres.',
            'inscripcion_numero.required' => 'El número de inscripción es obligatorio.',
            'inscripcion_numero.max' => 'El número de inscripción debe tener un máximo de 255 caracteres.',
            'inscripcion_anio.required' => 'El año de inscripción es obligatorio.',
            'inscripcion_anio.max' => 'El año de inscripción debe tener un máximo de 255 caracteres.',
            'rol_avaluo.max' => 'El rol de avaluo debe tener un máximo de 255 caracteres.',
            'superficie.max' => 'La superficie debe tener un máximo de 255 caracteres.',
            'deslinde_norte.max' => 'El deslinde norte debe tener un máximo de 255 caracteres.',
            'deslinde_sur.max' => 'El deslinde sur debe tener un máximo de 255 caracteres.',
            'deslinde_este.max' => 'El deslinde este debe tener un máximo de 255 caracteres.',
            'deslinde_oeste.max' => 'El deslinde oeste debe tener un máximo de 255 caracteres.',
            'decreto_incorporacion.max' => 'El decreto de incorporación debe tener un máximo de 255 caracteres.',
            'decreto_destinacion.max' => 'El decreto de destinación debe tener un máximo de 255 caracteres.',
            'observaciones.max' => 'Las observaciones deben tener un máximo de 1000 caracteres.',
        ];
    }
}
