<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->getTotalAmount(),
            'amount_executed' => $this->getTotalItemsExecutedAmount(),
            'project_number' => $this->project_number,
            'token' => $this->token,
            'purchase_plan_id' => $this->purchase_plan_id,
            'unit_purchasing' => $this->unitPurchasing,
            'type_project' => $this->typeProject,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'execution_percentage' => $this->getExecutionItemsPercentage(),
        ];
    }
} 