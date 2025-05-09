<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplainantResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rut' => $this->rut,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'charge' => $this->charge,
            'unit' => $this->unit,
            'function' => $this->function,
            'grade_eur' => $this->grade_eur,
            'date_income' => $this->date_income ? Carbon::parse($this->date_income)->format('d-m-Y H:i:s') : null,
            'type_contract' => $this->type_contract,
            'type_ladder' => $this->type_ladder,
            'grade' => $this->grade,
            'is_victim' => $this->is_victim,
            'dependence_id' => $this->dependence_id,

            
            // Relaciones con otras tablas
            'dependence' => new DependenceResource($this->whenLoaded('dependence')),
        ];
    }
}
