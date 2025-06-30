<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePlanStatusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => new StatusPurchasePlanResource($this->status),
            'sending_date' => $this->sending_date,
            'created_by' => new UserResource($this->createdBy),
            'created_at' => $this->created_at,
        ];
    }
}
