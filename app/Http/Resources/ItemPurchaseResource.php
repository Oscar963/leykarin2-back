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
            'regional_distribution' => $this->regional_distribution,
            'cod_budget_allocation_type' => $this->cod_budget_allocation_type,
            'budget_allocation_id' => $this->budget_allocation_id,
            'type_purchase_id' => $this->type_purchase_id,
            'budget_allocation' => $this->budgetAllocation,
            'type_purchase' => $this->typePurchase,
            'project' => new ProjectResource($this->project),
            'status' => $this->statusItemPurchase,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_item' => $this->getTotalAmount(),
        ];
    }
}
