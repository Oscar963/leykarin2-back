<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DenouncedResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'charge' => $this->charge,
            'grade' => $this->grade,
            'email' => $this->email,
            'unit' => $this->unit,
            'function' => $this->function,
        ];
    }
}
