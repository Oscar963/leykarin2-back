<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnexoResource extends JsonResource
{

    public function toArray($request)
    {
        return array_filter([
            'id' => $this->id,
            'internal_number' => $this->internal_number,
            'external_number' => $this->external_number,
            'office' => $this->office,
            'unit' => $this->unit,
            'person' => $this->person,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], function ($value) {
            return !is_null($value); // Filtra los valores nulos
        });
    }
}
