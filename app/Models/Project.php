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

    public function typeProject()
    {
        return $this->belongsTo(TypeProject::class, 'type_project_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getTotalAmount()
    {
        return $this->itemPurchases->sum(function ($item) {
            return $item->getTotalAmount();
        });
    }

    public function getNextItemNumber()
    {
        $lastItem = $this->itemPurchases()
            ->orderBy('item_number', 'desc')
            ->first();

        return $lastItem ? $lastItem->item_number + 1 : 1;
    }

    public function getTotalItemsExecutedAmount()
    {
        return $this->itemPurchases->filter(function ($item) {
            $status = strtolower($item->statusItemPurchase->name ?? '');
            return $status === 'pagado' || $status === 'comprado';
        })->sum(function ($item) {
            return $item->getTotalAmount();
        });
    }

    public function getExecutionItemsPercentage()
    {
        $items = $this->itemPurchases;
        if ($items->isEmpty()) {
            return 0;
        }

        $totalItems = $items->count();
        $completedItems = $items->filter(function ($item) {
            $status = strtolower($item->statusItemPurchase->name ?? '');
            return $status === 'pagado' || $status === 'comprado';
        })->count();

        $percentage = ($completedItems / $totalItems) * 100;

        return round($percentage, 2);
    }
}
