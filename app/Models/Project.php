<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'project_number',
        'description',
        'created_by',
        'updated_by',
        'unit_purchasing_id',
        'purchase_plan_id',
        'type_project_id'
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

    public function mediaVerifiers()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Relación con las metas (solo para proyectos estratégicos)
     */
    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Verifica si el proyecto es estratégico
     */
    public function isStrategic()
    {
        return $this->typeProject && $this->typeProject->name === 'Estratégico';
    }

    /**
     * Verifica si el proyecto es operativo
     */
    public function isOperative()
    {
        return $this->typeProject && $this->typeProject->name === 'Operativo';
    }

    /**
     * Obtiene las metas del proyecto (solo si es estratégico)
     */
    public function getGoals()
    {
        if (!$this->isStrategic()) {
            return collect();
        }
        
        return $this->goals;
    }

    /**
     * Obtiene el progreso promedio de las metas (solo proyectos estratégicos)
     */
    public function getAverageGoalProgress()
    {
        if (!$this->isStrategic() || $this->goals->isEmpty()) {
            return 0;
        }

        $totalProgress = $this->goals->sum(function ($goal) {
            return $goal->getProgressPercentage();
        });

        return round($totalProgress / $this->goals->count(), 2);
    }

    /**
     * Obtiene el número de metas completadas
     */
    public function getCompletedGoalsCount()
    {
        if (!$this->isStrategic()) {
            return 0;
        }

        return $this->goals->filter(function ($goal) {
            return $goal->isCompleted();
        })->count();
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
            return $status === 'pagado' || $status === 'rendido';
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
            return $status === 'pagado' || $status === 'rendido';
        })->count();

        $percentage = ($completedItems / $totalItems) * 100;

        return round($percentage, 2);
    }
}
