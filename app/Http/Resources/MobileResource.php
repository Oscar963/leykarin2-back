<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'office' => $this->office,
            'direction' => $this->direction,
            'person' => $this->person,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
