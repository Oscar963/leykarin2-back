<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modification extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'modifications';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'description',
        'version',
        'date',
        'status',
        'modification_type_id',
        'purchase_plan_id',
        'created_by'
    ];

    /**
     * Campos que deben ser convertidos a tipos específicos
     */
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Estados disponibles para las modificaciones
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relación muchos a uno con ModificationType
     * Una modificación pertenece a un tipo de modificación
     */
    public function modificationType()
    {
        return $this->belongsTo(ModificationType::class);
    }

    /**
     * Relación muchos a uno con PurchasePlan
     * Una modificación pertenece a un plan de compra
     */
    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    /**
     * Usuario que creó la modificación
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Verifica si la modificación está activa
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica si la modificación está pendiente
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la modificación está aprobada
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verifica si la modificación está rechazada
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Verifica si la modificación está inactiva
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Obtiene todos los estados disponibles
     *
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado'
        ];
    }

    /**
     * Scope para filtrar por estado
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por tipo de modificación
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $modificationTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModificationType($query, int $modificationTypeId)
    {
        return $query->where('modification_type_id', $modificationTypeId);
    }

    /**
     * Scope para filtrar por nombre del tipo de modificación
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $typeName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModificationTypeName($query, string $typeName)
    {
        return $query->whereHas('modificationType', function ($q) use ($typeName) {
            $q->where('name', 'like', "%{$typeName}%");
        });
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

    /**
     * Scope para obtener modificaciones pendientes de aprobación
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para obtener modificaciones aprobadas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope para obtener modificaciones activas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope para obtener modificaciones por usuario creador
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope para buscar por nombre
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope para buscar por descripción
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $description
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDescription($query, string $description)
    {
        return $query->where('description', 'like', "%{$description}%");
    }

    /**
     * Scope para filtrar por plan de compra
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $purchasePlanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPurchasePlan($query, int $purchasePlanId)
    {
        return $query->where('purchase_plan_id', $purchasePlanId);
    }
} 