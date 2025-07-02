<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModificationHistory extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'modification_histories';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'modification_id',
        'action',
        'description',
        'details',
        'user_id',
        'date'
    ];

    /**
     * Campos que deben ser convertidos a tipos específicos
     */
    protected $casts = [
        'details' => 'array',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Acciones disponibles para el historial
     */
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_STATUS_CHANGE = 'status_change';

    /**
     * Relación muchos a uno con Modification
     * Un registro de historial pertenece a una modificación
     */
    public function modification()
    {
        return $this->belongsTo(Modification::class);
    }

    /**
     * Usuario que realizó la acción
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene todas las acciones disponibles
     *
     * @return array
     */
    public static function getAvailableActions(): array
    {
        return [
            self::ACTION_CREATE => 'Crear',
            self::ACTION_UPDATE => 'Actualizar',
            self::ACTION_DELETE => 'Eliminar',
            self::ACTION_STATUS_CHANGE => 'Cambio de Estado'
        ];
    }

    /**
     * Scope para filtrar por acción
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por modificación
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $modificationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModification($query, int $modificationId)
    {
        return $query->where('modification_id', $modificationId);
    }

    /**
     * Scope para filtrar por usuario
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por rango de fechas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
} 