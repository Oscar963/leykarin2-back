<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportHistoriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'filename' => 'required|string',
            'imported_by' => 'required|exists:users,id',
            'status' => 'required|in:pending,processing,completed,failed',
            'total_rows' => 'required|integer',
            'success_count' => 'required|integer',
            'error_count' => 'required|integer',
            'error_log' => 'nullable|array',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
        ];
    }
}
