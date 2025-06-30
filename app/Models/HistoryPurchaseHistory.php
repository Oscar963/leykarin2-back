<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryPurchaseHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'description',
        'user',
        'purchase_plan_id',
        'status_purchase_plan_id',
        'action_type',
        'details'
    ];

    protected $casts = [
        'date' => 'datetime',
        'details' => 'array'
    ];

    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    public function status()
    {
        return $this->belongsTo(StatusPurchasePlan::class, 'status_purchase_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public static function logAction($purchasePlanId, $actionType, $description, $details = null)
    {
        $user = auth()->user();
        $purchasePlan = PurchasePlan::find($purchasePlanId);

        return self::create([
            'date' => now(),
            'description' => $description,
            'user' => $user ? $user->name : 'Sistema',
            'purchase_plan_id' => $purchasePlanId,
            'status_purchase_plan_id' => $purchasePlan ? $purchasePlan->getCurrentStatusId() : 1,
            'action_type' => $actionType,
            'details' => $details
        ]);
    }
}
