<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlan extends Model
{
    use HasFactory;

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function status()
    {
        return $this->belongsTo(StatusPurchasePlan::class, 'status_purchase_plan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'purchase_plan_id');
    }

    public function decreto()
    {
        return $this->belongsTo(File::class, 'decreto_id');
    }

    public function formF1()
    {
        return $this->belongsTo(FormF1::class, 'form_f1_id');
    }

    public function getNextProjectNumber()
    {
        $lastProject = $this->projects()
            ->orderBy('project_number', 'desc')
            ->first();

        return $lastProject ? $lastProject->project_number + 1 : 1;
    }

    /**
     * Calcula el presupuesto disponible restando la suma de los montos de todos los proyectos del amount del FormF1
     * 
     * @return float
     */
    public function getAvailableBudget()
    {
        $totalProjectsAmount = $this->projects()
            ->with('itemPurchases')
            ->get()
            ->sum(function ($project) {
                return $project->getTotalAmount();
            });

        $formF1Amount = $this->formF1 ? $this->formF1->amount : 0;
        return $formF1Amount - $totalProjectsAmount;
    }

    public function getTotalAmount()
    {
        return $this->projects->sum(function($project) {
            return $project->getTotalAmount();
        });
    }

    public function getTotalProjectsExecutedAmount()
    {
        return $this->projects->sum(function($project) {
            return $project->getTotalItemsExecutedAmount();
        });
    }
    
    public function getTotalProjectsExecutedPercentage()
    {
        $projects = $this->projects;
        if ($projects->isEmpty()) {
            return 0;
        }

        $sumPercentages = $projects->sum(function($project) {
            return $project->getExecutionItemsPercentage();
        });

        $avg = $sumPercentages / $projects->count();
        return fmod($avg, 1) == 0.0 ? (int)$avg : round($avg, 2);
    }

}
