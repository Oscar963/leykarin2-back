<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_number',
        'product_service',
        'quantity_item',
        'amount_item',
        'quantity_oc',
        'months_oc',
        'regional_distribution',
        'cod_budget_allocation_type',
        'comment',
        'project_id',
        'budget_allocation_id',
        'type_purchase_id',
        'status_item_purchase_id',
        'publication_month_id',
        'created_by',
        'updated_by',
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

    /**
     * Relación con PublicationMonth
     */
    public function publicationMonth(): BelongsTo
    {
        return $this->belongsTo(PublicationMonth::class);
    }

    /**
     * Accessor para obtener el formato "Dic 2025"
     */
    public function getPublicationDateFormattedAttribute(): ?string
    {
        if ($this->publicationMonth) {
            return $this->publicationMonth->formatted_date;
        }
        return null;
    }

    public function getTotalAmount()
    {
        return $this->amount_item * $this->quantity_item;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
