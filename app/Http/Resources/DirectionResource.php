<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DirectionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'director_id' => $this->director_id,
            'director' => $this->whenLoaded('director'),
            'users' => $this->whenLoaded('users'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
