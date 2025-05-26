<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_service',
        'quantity_item',
        'amount_item',
    ];

    public function budgetAllocation()
    {
        return $this->belongsTo(BudgetAllocation::class, 'budget_allocation_id');
    }

    public function typePurchase()
    {
        return $this->belongsTo(TypePurchase::class, 'type_purchase_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function statusItemPurchase()
    {
        return $this->belongsTo(StatusItemPurchase::class, 'status_item_purchase_id');
    }
}
