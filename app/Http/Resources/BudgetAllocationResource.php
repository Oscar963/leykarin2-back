<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BudgetAllocationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'code' => $this->code,
            'cod_budget_allocation_type' => $this->cod_budget_allocation_type,
        ];
    }
}
