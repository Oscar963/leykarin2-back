<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert flat witnesses data to nested array format
        $witnesses = [];
        $witnessIndex = 0;

        while (
            $this->has("witnesses.{$witnessIndex}.name") ||
            $this->has("witnesses.{$witnessIndex}.phone") ||
            $this->has("witnesses.{$witnessIndex}.email")
        ) {

            $witness = [];
            if ($this->has("witnesses.{$witnessIndex}.name")) {
                $witness['name'] = $this->input("witnesses.{$witnessIndex}.name");
            }
            if ($this->has("witnesses.{$witnessIndex}.phone")) {
                $witness['phone'] = $this->input("witnesses.{$witnessIndex}.phone");
            }
            if ($this->has("witnesses.{$witnessIndex}.email")) {
                $witness['email'] = $this->input("witnesses.{$witnessIndex}.email");
            }

            if (!empty($witness)) {
                $witnesses[] = $witness;
            }

            $witnessIndex++;
        }

        if (!empty($witnesses)) {
            $this->merge(['witnesses' => $witnesses]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // Complaint fields
            'type_complaint_id' => 'required|integer|exists:type_complaints,id',
            'hierarchical_level_id' => 'required|integer|exists:hierarchical_levels,id',
            'work_relationship_id' => 'required|integer|exists:work_relationships,id',
            'supervisor_relationship_id' => 'required|integer|exists:supervisor_relationships,id',
            'circumstances_narrative' => 'required|string',
            'consequences_narrative' => 'required|string',

            // Complainant fields
            'complainant_dependence_id' => 'required|integer|exists:type_dependencies,id',
            'complainant_name' => 'required|string|max:255',
            'complainant_address' => 'required|string|max:255',
            'complainant_rut' => 'required|string|max:20',
            'complainant_phone' => 'required|string|max:20',
            'complainant_charge' => 'required|string|max:255',
            'complainant_email' => 'required|email|max:255',
            'complainant_confirm_email' => 'required|email|same:complainant_email',
            'complainant_unit' => 'required|string|max:255',
            'complainant_function' => 'required|string|max:255',
            'complainant_grade' => 'nullable|integer',
            'complainant_birthdate' => 'required|date',
            'complainant_entry_date' => 'required|date',
            'complainant_contractual_status' => 'required|string|max:255',
            'complainant_is_victim' => 'required|boolean',

            // Denounced fields
            'denounced_name' => 'required|string|max:255',
            'denounced_address' => 'nullable|string|max:255',
            'denounced_rut' => 'nullable|string|max:20',
            'denounced_phone' => 'nullable|string|max:20',
            'denounced_charge' => 'required|string|max:255',
            'denounced_email' => 'nullable|email|max:255',
            'denounced_confirm_email' => 'nullable|email|same:denounced_email',
            'denounced_unit' => 'required|string|max:255',
            'denounced_function' => 'required|string|max:255',
            'denounced_grade' => 'nullable|integer',

            // Witnesses fields
            'witnesses' => 'nullable|array',
            'witnesses.*.name' => 'nullable|string|max:255',
            'witnesses.*.phone' => 'nullable|string|max:50',
            'witnesses.*.email' => 'nullable|email|max:255',
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
            'type_complaint_id' => 'Tipo de denuncia',
            'hierarchical_level_id' => 'Nivel jerárquico',
            'work_relationship_id' => 'Relación laboral',
            'supervisor_relationship_id' => 'Relación de supervisión',
            'circumstances_narrative' => 'Narrativa de circunstancias',
            'consequences_narrative' => 'Narrativa de consecuencias',
            'complainant_dependence_id' => 'Dependencia del denunciante',
            'complainant_name' => 'Nombre del denunciante',
            'complainant_address' => 'Dirección del denunciante',
            'complainant_rut' => 'RUT del denunciante',
            'complainant_phone' => 'Teléfono del denunciante',
            'complainant_charge' => 'Cargo del denunciante',
            'complainant_email' => 'Email del denunciante',
            'complainant_confirm_email' => 'Confirmación de email del denunciante',
            'complainant_unit' => 'Unidad del denunciante',
            'complainant_function' => 'Función del denunciante',
            'complainant_grade' => 'Grado del denunciante',
            'complainant_is_victim' => 'Es víctima',
            'denounced_name' => 'Nombre del denunciado',
            'denounced_address' => 'Dirección del denunciado',
            'denounced_rut' => 'RUT del denunciado',
            'denounced_phone' => 'Teléfono del denunciado',
            'denounced_charge' => 'Cargo del denunciado',
            'denounced_email' => 'Email del denunciado',
            'denounced_confirm_email' => 'Confirmación de email del denunciado',
            'denounced_unit' => 'Unidad del denunciado',
            'denounced_function' => 'Función del denunciado',
            'denounced_grade' => 'Grado del denunciado',
            'witnesses.*.name' => 'Nombre del testigo',
            'witnesses.*.phone' => 'Teléfono del testigo',
            'witnesses.*.email' => 'Email del testigo',
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
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un email válido.',
            'same' => 'El campo :attribute debe coincidir con :other.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'max' => 'El campo :attribute no debe exceder :max caracteres.',
        ];
    }
}
