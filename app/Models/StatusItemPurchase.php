<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusItemPurchase extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function itemPurchases()
    {
        return $this->hasMany(ItemPurchase::class, 'status_item_purchase_id');
    }
}
