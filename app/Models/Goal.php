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
        'current_value' => 'decimal:2'
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
     * Calcula el porcentaje de progreso de la meta
     */
    public function getProgressPercentage()
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        $percentage = ($this->current_value / $this->target_value) * 100;
        return min(100, max(0, round($percentage, 2)));
    }

    /**
     * Verifica si la meta está completada
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED || 
               ($this->target_value && $this->current_value >= $this->target_value);
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
    public function updateProgress($currentValue, $notes = null)
    {
        $this->current_value = $currentValue;
        
        if ($notes) {
            $this->notes = $notes;
        }

        // Actualizar estado automáticamente
        if ($this->isCompleted() && $this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($currentValue > 0 && $this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_IN_PROGRESS;
        }

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