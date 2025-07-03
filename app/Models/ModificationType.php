<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModificationType extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'modification_types';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Relación uno a muchos con Modification
     * Un tipo de modificación puede tener muchas modificaciones
     */
    public function modifications()
    {
        return $this->hasMany(Modification::class);
    }

    /**
     * Scope para filtrar por nombre
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    /**
     * Scope para filtrar por descripción
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $description
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDescription($query, string $description)
    {
        return $query->where('description', 'LIKE', "%{$description}%");
    }

    /**
     * Obtiene el número de modificaciones de este tipo
     *
     * @return int
     */
    public function getModificationsCount(): int
    {
        return $this->modifications()->count();
    }

    /**
     * Obtiene las modificaciones activas de este tipo
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveModifications()
    {
        return $this->modifications()->where('status', 'active')->get();
    }

    /**
     * Obtiene las modificaciones pendientes de este tipo
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingModifications()
    {
        return $this->modifications()->where('status', 'pending')->get();
    }

    /**
     * Obtiene las modificaciones aprobadas de este tipo
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApprovedModifications()
    {
        return $this->modifications()->where('status', 'approved')->get();
    }

    /**
     * Verifica si el tipo de modificación está siendo usado
     *
     * @return bool
     */
    public function isInUse(): bool
    {
        return $this->modifications()->exists();
    }

    /**
     * Obtiene estadísticas del tipo de modificación
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $modifications = $this->modifications();
        
        return [
            'total' => $modifications->count(),
            'active' => $modifications->where('status', 'active')->count(),
            'pending' => $modifications->where('status', 'pending')->count(),
            'approved' => $modifications->where('status', 'approved')->count(),
            'rejected' => $modifications->where('status', 'rejected')->count(),
            'inactive' => $modifications->where('status', 'inactive')->count(),
            'total_budget_impact' => $modifications->whereNotNull('budget_impact')->sum('budget_impact')
        ];
    }
} 