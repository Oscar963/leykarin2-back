<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type_complaint' => [
                'required',
            ],

            // Datos del denunciante
            'dependence_complainant' => [
                'required',
                'string',
                'max:100',
            ],
            'name_complainant'         => 'required|string|max:50',
            'rut_complainant'          => 'required|string|max:15',
            'phone_complainant'        => 'required|string|max:15',
            'email_complainant'        => 'required|email|max:50',
            'address_complainant'      => 'required|string|max:50',
            'charge_complainant'       => 'required|string|max:50',
            'unit_complainant'         => 'required|string|max:50',
            'function_complainant'     => 'required|string|max:100',
            'grade_eur_complainant'    => 'nullable|integer|min:0|max:255',
            'date_income_complainant'  => 'required|date',
            'type_contract_complainant' => [
                'required',
                'string',
                'in:planta,contrata,honorario,codigo',
            ],
            'grade_complainant'        => 'nullable|integer|min:0|max:255',
            'type_ladder_complainant' => [
                'required',
                'string',
                'in:directivo,jefatura,profesional,tecnico,administrativo,auxiliar,no-aplica',
            ],
            'is_victim_complainant'    => 'required|boolean',

            // Datos del denunciado
            'name_denounced'         => 'required|string|max:50',
            'rut_denounced'          => 'nullable|string|max:15',
            'phone_denounced'        => 'nullable|string|max:15',
            'address_denounced'      => 'nullable|string|max:50',
            'charge_denounced'       => 'required|string|max:50',
            'grade_denounced'        => 'nullable|integer|min:0|max:255',
            'email_denounced'        => 'nullable|email|max:50',
            'unit_denounced'         => 'required|string|max:50',
            'function_denounced'     => 'required|string|max:100',

            // Datos de la denuncia
            'hierarchical_level' => [
                'required',
                'in:nivel_inferior,igual_nivel,nivel_superior,externo',
            ],
            'work_directly' => [
                'required',
                'in:si,no,ocasionalmente',
            ],
            'immediate_leadership' => [
                'required',
                'in:si,no',
            ],
            'narration_facts'     => 'required|string|max:20000',
            'narration_consequences'     => 'required|string|max:20000',

            // Validación para evidencias
            'evidences' => 'array',
            'evidences.*.name' => 'required|string|max:255',
            'evidences.*.file' => [
                'required',
                'file',
                'max:4096', // Tamaño en kilobytes (4096 KB = 4 MB)
                'mimes:doc,docx,xls,xlsx,pdf,jpg,jpeg,png'
            ],

            // Datos de los testigos
            'witnesses' => 'array',
            'witnesses.*.name' => 'required|string|max:255',
            'witnesses.*.email' => 'nullable|email',
            'witnesses.*.phone' => 'nullable|string',

            // Datos de la firma
            'signature' => [
                'required',
                'image',
                'max:2048', // Tamaño en kilobytes (2048 KB = 2 MB)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Datos del denunciante
            'type_complaint.required' => 'El tipo de denuncia es obligatorio.',
            'type_complaint.in' => 'El tipo de denuncia no es válido.',

            'dependence_complainant.required' => 'La dependencia del denunciante es obligatoria.',
            'dependence_complainant.string'   => 'La dependencia debe ser un texto.',
            'dependence_complainant.max'      => 'La dependencia no debe exceder 100 caracteres.',
            'dependence_complainant.in' => 'La dependencia seleccionada no es válida. Solo se permiten: ima, demuce, disam.',

            'name_complainant.required' => 'El nombre del denunciante es obligatorio.',
            'name_complainant.string'   => 'El nombre debe ser un texto.',
            'name_complainant.max'      => 'El nombre no debe exceder 50 caracteres.',

            'rut_complainant.required' => 'El RUT del denunciante es obligatorio.',
            'rut_complainant.string'   => 'El RUT debe ser un texto.',
            'rut_complainant.max'      => 'El RUT no debe exceder 15 caracteres.',

            'phone_complainant.required' => 'El teléfono del denunciante es obligatorio.',
            'phone_complainant.string'   => 'El teléfono debe ser un texto.',
            'phone_complainant.max'      => 'El teléfono no debe exceder 15 caracteres.',

            'email_complainant.required' => 'El correo electrónico del denunciante es obligatorio.',
            'email_complainant.email'    => 'Debe ser un correo electrónico válido.',
            'email_complainant.max'      => 'El correo electrónico no debe exceder 50 caracteres.',

            'address_complainant.required' => 'La dirección del denunciante es obligatoria.',
            'address_complainant.string'   => 'La dirección debe ser un texto.',
            'address_complainant.max'      => 'La dirección no debe exceder 50 caracteres.',

            'charge_complainant.required' => 'El cargo del denunciante es obligatorio.',
            'charge_complainant.string'   => 'El cargo debe ser un texto.',
            'charge_complainant.max'      => 'El cargo no debe exceder 50 caracteres.',

            'unit_complainant.required' => 'La unidad del denunciante es obligatoria.',
            'unit_complainant.string'   => 'La unidad debe ser un texto.',
            'unit_complainant.max'      => 'La unidad no debe exceder 50 caracteres.',

            'function_complainant.required' => 'La función del denunciante es obligatoria.',
            'function_complainant.string'   => 'La función debe ser un texto.',
            'function_complainant.max'      => 'La función no debe exceder 100 caracteres.',

            'grade_eur_complainant.integer' => 'El grado EUR debe ser un número entero.',
            'grade_eur_complainant.min'     => 'El grado EUR debe ser mayor o igual a 0.',
            'grade_eur_complainant.max'     => 'El grado EUR no debe ser mayor a 255.',

            'date_income_complainant.required' => 'La fecha de ingreso es obligatoria.',
            'date_income_complainant.date'     => 'La fecha de ingreso debe ser una fecha válida.',

            'type_contract_complainant.required' => 'El tipo de contrato es obligatorio.',
            'type_contract_complainant.string'   => 'El tipo de contrato debe ser un texto.',
            'type_contract_complainant.in' => 'El tipo de contrato no es válido.',

            'grade_complainant.integer' => 'El grado debe ser un número entero.',
            'grade_complainant.min'     => 'El grado debe ser mayor o igual a 0.',
            'grade_complainant.max'     => 'El grado no debe ser mayor a 255.',

            'type_ladder_complainant.required' => 'El tipo de escalafón es obligatorio.',
            'type_ladder_complainant.string'   => 'El tipo de escalafón debe ser un texto.',
            'type_ladder_complainant.in' => 'El tipo de escalafón no es válido.',

            'is_victim_complainant.required' => 'Debe indicar si el denunciante es la víctima.',
            'is_victim_complainant.boolean'  => 'El campo de víctima debe ser verdadero o falso.',

            // Datos del denunciado
            'name_denounced.required' => 'El nombre del denunciado es obligatorio.',
            'name_denounced.string'   => 'El nombre del denunciado debe ser un texto.',
            'name_denounced.max'      => 'El nombre del denunciado no debe exceder 50 caracteres.',

            'rut_denounced.string' => 'El RUT del denunciado debe ser un texto.',
            'rut_denounced.max'    => 'El RUT del denunciado no debe exceder 15 caracteres.',

            'phone_denounced.string' => 'El teléfono del denunciado debe ser un texto.',
            'phone_denounced.max'    => 'El teléfono del denunciado no debe exceder 15 caracteres.',

            'address_denounced.string' => 'La dirección del denunciado debe ser un texto.',
            'address_denounced.max'    => 'La dirección del denunciado no debe exceder 50 caracteres.',

            'charge_denounced.required' => 'El cargo del denunciado es obligatorio.',
            'charge_denounced.string'   => 'El cargo del denunciado debe ser un texto.',
            'charge_denounced.max'      => 'El cargo del denunciado no debe exceder 50 caracteres.',

            'grade_denounced.integer' => 'El grado del denunciado debe ser un número entero.',
            'grade_denounced.min'     => 'El grado del denunciado debe ser mayor o igual a 0.',
            'grade_denounced.max'     => 'El grado del denunciado no debe ser mayor a 255.',

            'email_denounced.email' => 'El correo electrónico del denunciado debe ser válido.',
            'email_denounced.max'   => 'El correo electrónico del denunciado no debe exceder 50 caracteres.',

            'unit_denounced.required' => 'La unidad del denunciado es obligatoria.',
            'unit_denounced.string'   => 'La unidad del denunciado debe ser un texto.',
            'unit_denounced.max'      => 'La unidad del denunciado no debe exceder 50 caracteres.',

            'function_denounced.required' => 'La función del denunciado es obligatoria.',
            'function_denounced.string'   => 'La función del denunciado debe ser un texto.',
            'function_denounced.max'      => 'La función del denunciado no debe exceder 100 caracteres.',

            // Datos de la denuncia
            'hierarchical_level.required' => 'El nivel jerárquico es obligatorio.',
            'hierarchical_level.in'       => 'El nivel jerárquico seleccionado no es válido. Debe ser uno de: nivel_inferior, igual_nivel, nivel_superior, externo.',

            'work_directly.required' => 'Debe indicar si trabaja directamente con el denunciado.',
            'work_directly.in'       => 'La respuesta de trabajo directo no es válida. Debe ser uno de: si, no, ocasionalmente.',

            'immediate_leadership.required' => 'Debe indicar si el denunciado es su jefatura directa.',
            'immediate_leadership.in'       => 'La respuesta sobre jefatura directa no es válida. Debe ser uno de: si o no.',

            'narration_facts.required' => 'La narración de los hechos es obligatoria.',
            'narration_facts.string'   => 'La narración de los hechos debe ser un texto.',
            'narration_facts.max'      => 'La narración de los hechos no debe exceder 20000 caracteres.',

            'narration_consequences.required' => 'La narración de las consecuencias es obligatoria.',
            'narration_consequences.string'   => 'La narración de las consecuencias debe ser un texto.',
            'narration_consequences.max'      => 'La narración de las consecuencias no debe exceder 20000 caracteres.',

            // Evidencias
            'evidences.*.name.required' => 'El nombre de la evidencia es obligatorio.',
            'evidences.*.name.string' => 'El nombre de la evidencia debe ser un texto.',
            'evidences.*.name.max' => 'El nombre de la evidencia no debe exceder los 255 caracteres.',

            'evidences.*.file.required' => 'El archivo de evidencia es obligatorio.',
            'evidences.*.file.file' => 'El archivo de evidencia debe ser un archivo válido.',
            'evidences.*.file.max' => 'El archivo de evidencia no debe superar los 4 MB.',
            'evidences.*.file.mimes' => 'El archivo de evidencia debe ser un documento o imagen válida (doc, docx, xls, xlsx, pdf, jpg, jpeg, png).',

            // Testigos
            'witnesses.*.name.required' => 'El nombre del testigo es obligatorio.',
            'witnesses.*.name.string' => 'El nombre del testigo debe ser un texto.',
            'witnesses.*.name.max' => 'El nombre del testigo no debe exceder los 255 caracteres.',

            'witnesses.*.email.email' => 'El correo del testigo debe ser una dirección válida.',
            'witnesses.*.phone.string' => 'El número de teléfono del testigo debe ser un texto.',

            // Firma
            'signature.required' => 'La firma es obligatoria.',
            'signature.image' => 'La firma debe ser una imagen válida (jpg, jpeg, png).',
            'signature.max' => 'La firma no debe superar los 2 MB.',

        ];
    }
}
