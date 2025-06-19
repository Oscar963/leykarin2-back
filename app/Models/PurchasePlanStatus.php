<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlanStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_plan_id',
        'status_purchase_plan_id',
        'sending_date',
        'sending_comment',
        'created_by'
    ];

    protected $casts = [
        'sending_date' => 'datetime',       
    ];

    /**
     * Relación con el plan de compra
     */
    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    /**
     * Relación con el estado
     */
    public function status()
    {
        return $this->belongsTo(StatusPurchasePlan::class, 'status_purchase_plan_id');
    }

    /**
     * Usuario que creó el registro
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el estado actual de un plan de compra
     */
    public static function getCurrentStatus($purchasePlanId)
    {
        return self::where('purchase_plan_id', $purchasePlanId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Obtiene el historial completo de estados de un plan de compra
     */
    public static function getStatusHistory($purchasePlanId)
    {
        return self::where('purchase_plan_id', $purchasePlanId)
            ->with(['status', 'createdBy'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
} 