<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPurchasePlan extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relación muchos a muchos con planes de compra a través de la tabla pivote
     */
    public function purchasePlans()
    {
        return $this->hasMany(PurchasePlanStatus::class);
    }

    /**
     * Obtiene todos los planes de compra que tienen este estado
     */
    public function getPurchasePlansWithThisStatus()
    {
        return $this->purchasePlans()->with('purchasePlan')->get()->pluck('purchasePlan');
    }
}
