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
}
