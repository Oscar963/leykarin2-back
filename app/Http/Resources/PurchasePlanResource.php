<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePlanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date_created' => $this->date_created,
            'amount_F1' => $this->amount_F1,
            'token' => $this->token,
            'year' => $this->year,
            'decreto' => $this->decreto,
            'formF1' => $this->formF1,
            'sending_date' => $this->sending_date,
            'modification_date' => $this->modification_date,
            'status_purchase_plan_id' => $this->status_purchase_plan_id,
            'status' => $this->status,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'available_budget' => $this->getAvailableBudget(),
            'total_amount' => $this->getTotalAmount(),
            'total_executed_amount' => $this->getTotalProjectsExecutedAmount(),
            'total_executed_percentage' => $this->getTotalProjectsExecutedPercentage(),
        ];
    }
}
