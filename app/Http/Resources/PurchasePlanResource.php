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
            'token' => $this->token,
            'year' => $this->year,
            'decreto' => $this->decreto,
            'formF1' => new FormF1Resource($this->formF1),
            'sending_date' => $this->sending_date,
            'modification_date' => $this->modification_date,
            'current_status' => $this->whenLoaded('currentStatus', function() {
                return [
                    'id' => $this->getCurrentStatusId(),
                    'name' => $this->getCurrentStatusName(),
                    'status_data' => $this->getCurrentStatus()
                ];
            }),
            'status_history' => $this->whenLoaded('statusHistory', function() {
                return $this->statusHistory->map(function($status) {
                    return [
                        'id' => $status->id,
                        'status_id' => $status->status_purchase_plan_id,
                        'status_name' => $status->status->name ?? null,
                        'sending_date' => $status->sending_date,
                        'plan_name' => $status->plan_name,
                        'plan_year' => $status->plan_year,
                        'total_amount' => $status->total_amount,
                        'available_budget' => $status->available_budget,
                        'sending_comment' => $status->sending_comment,
                        'created_by' => $status->createdBy,
                        'created_at' => $status->created_at
                    ];
                });
            }),
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
