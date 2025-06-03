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
        return $this->belongsTo(StatusPlan::class);
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
        return $this->belongsTo(File::class, 'form_F1_id');
    }

    public function getNextProjectNumber()
    {
        $lastProject = $this->projects()
            ->orderBy('project_number', 'desc')
            ->first();

        return $lastProject ? $lastProject->project_number + 1 : 1;
    }

    /**
     * Calcula el presupuesto disponible restando la suma de los montos de todos los proyectos del amount_F1
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

        return $this->amount_F1 - $totalProjectsAmount;
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
