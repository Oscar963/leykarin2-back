<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemPurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_service' => $this->product_service,
            'quantity_item' => $this->quantity_item,
            'amount_item' => $this->amount_item,
            'item_number' => $this->item_number,
            'quantity_oc' => $this->quantity_oc,
            'months_oc' => $this->months_oc,
            'regional_distribution' =>  $this->regional_distribution,
            'cod_budget_allocation_type' =>  $this->cod_budget_allocation_type,
            'total_item' => $this->getTotalAmount(),
            'budget_allocation' => new BudgetAllocationResource($this->budgetAllocation),
            'type_purchase' => new TypePurchaseResource($this->typePurchase),
            'project' => new ProjectResource($this->project),
            'status' => new StatusItemPurchaseResource($this->statusItemPurchase),
            'publication_month' => new PublicationMonthResource($this->publicationMonth),
            'created_by' => new UserResource($this->createdBy),
            'updated_by' => new UserResource($this->updatedBy),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
