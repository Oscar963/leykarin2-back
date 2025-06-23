<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_service',
        'quantity_item',
        'amount_item',
        'publication_month_id',
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
