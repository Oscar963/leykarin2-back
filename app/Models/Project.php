<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class, 'purchase_plan_id');
    }

    public function itemPurchases()
    {
        return $this->hasMany(ItemPurchase::class, 'project_id');
    }

    public function unitPurchasing()
    {
        return $this->belongsTo(UnitPurchasing::class, 'unit_purchasing_id');
    }

    public function getTotalAmount()
    {
        return $this->itemPurchases()->sum('amount_item');
    }

    public function getNextItemNumber()
    {
        $lastItem = $this->itemPurchases()
            ->orderBy('item_number', 'desc')
            ->first();
            
        return $lastItem ? $lastItem->item_number + 1 : 1;
    }

    public function getExecutionPercentage()
    {
        $items = $this->itemPurchases;
        if ($items->isEmpty()) {
            return 0;
        }

        $totalItems = $items->count();
        $completedItems = $items->filter(function($item) {
            return $item->statusItemPurchase->name === 'Pagado';
        })->count();

        $percentage = ($completedItems / $totalItems) * 100;
        
        return round($percentage, 2);
    }
} 