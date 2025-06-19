<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoryPurchaseHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'description' => $this->description,
            'user' => $this->user,
            'action_type' => $this->action_type,
            'details' => $this->details,
            'status' => $this->whenLoaded('status', function() {
                return [
                    'id' => $this->status->id,
                    'name' => $this->status->name
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 