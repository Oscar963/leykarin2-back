<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'rut' => $this->rut,
            'email' => $this->email,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->format('d-m-Y H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
        ];
    }
}
