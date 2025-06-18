<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePlanStatusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_plan_id' => $this->purchase_plan_id,
            'status_id' => $this->status_purchase_plan_id,
            'status_name' => $this->status->name ?? null,
            'sending_date' => $this->sending_date,
            'plan_name' => $this->plan_name,
            'plan_year' => $this->plan_year,
            'total_amount' => $this->total_amount,
            'available_budget' => $this->available_budget,
            'sending_comment' => $this->sending_comment,
            'created_by' => $this->whenLoaded('createdBy', function() {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 