<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_value',
        'unit_measure',
        'current_value',
        'progress_value',
        'target_date',
        'status',
        'notes',
        'project_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'target_date' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'progress_value' => 'decimal:2'
    ];

    /**
     * Estados disponibles para las metas
     */
    const STATUS_PENDING = 'pendiente';
    const STATUS_IN_PROGRESS = 'en_progreso';
    const STATUS_COMPLETED = 'completada';
    const STATUS_CANCELLED = 'cancelada';

    /**
     * Relación con el proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Usuario que creó la meta
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó la meta
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calcula el porcentaje de progreso de la meta basado en progress_value
     */
    public function getProgressPercentage()
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        $percentage = ($this->progress_value / $this->target_value) * 100;
        return min(100, max(0, round($percentage, 2)));
    }

    /**
     * Verifica si la meta está completada
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED || 
               ($this->target_value && $this->progress_value >= $this->target_value);
    }

    /**
     * Calcula automáticamente el estado de la meta basado en progreso y fecha
     */
    public function getCalculatedStatus()
    {
        if ($this->progress_value >= $this->target_value) {
            return self::STATUS_COMPLETED;
        } elseif ($this->target_date && now()->gt($this->target_date)) {
            return 'vencida';
        } elseif ($this->progress_value > 0) {
            return self::STATUS_IN_PROGRESS;
        } else {
            return self::STATUS_PENDING;
        }
    }

    /**
     * Verifica si la meta está en riesgo (próxima a vencer o con bajo progreso)
     */
    public function isAtRisk()
    {
        if ($this->isCompleted()) {
            return false;
        }

        // Meta vencida
        if ($this->target_date && now()->gt($this->target_date)) {
            return true;
        }

        // Meta próxima a vencer (menos de 30 días) con progreso menor al 70%
        if ($this->target_date) {
            $daysRemaining = now()->diffInDays($this->target_date, false);
            if ($daysRemaining <= 30 && $this->getProgressPercentage() < 70) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene el valor faltante para completar la meta
     */
    public function getRemainingValue()
    {
        if (!$this->target_value) {
            return 0;
        }

        $remaining = $this->target_value - $this->progress_value;
        return max(0, $remaining);
    }

    /**
     * Obtiene una descripción legible del progreso
     */
    public function getProgressDescription()
    {
        $progress = $this->getProgressPercentage();
        $unit = $this->unit_measure ? " {$this->unit_measure}" : '';
        
        return "{$this->progress_value}/{$this->target_value}{$unit} ({$progress}%)";
    }

    /**
     * Verifica si la meta está vencida
     */
    public function isOverdue()
    {
        return $this->target_date && 
               $this->target_date->isPast() && 
               !$this->isCompleted();
    }

    /**
     * Obtiene los días restantes hasta la fecha meta
     */
    public function getDaysRemaining()
    {
        if (!$this->target_date) {
            return null;
        }

        return now()->diffInDays($this->target_date, false);
    }

    /**
     * Actualiza el progreso de la meta
     */
    public function updateProgress($progressValue, $notes = null)
    {
        $this->progress_value = $progressValue;
        
        if ($notes) {
            $this->notes = $notes;
        }

        // Actualizar estado automáticamente basado en el nuevo cálculo
        $this->status = $this->getCalculatedStatus();

        $this->updated_by = auth()->id();
        $this->save();

        return $this;
    }

    /**
     * Scope para obtener solo metas de proyectos estratégicos
     */
    public function scopeStrategic($query)
    {
        return $query->whereHas('project.typeProject', function ($q) {
            $q->where('name', 'Estratégico');
        });
    }

    /**
     * Scope para obtener metas por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para obtener metas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('target_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }
} 