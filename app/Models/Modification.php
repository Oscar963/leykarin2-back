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
        'modification_number',
        'date',
        'reason',
        'status',
        'purchase_plan_id',
        'created_by',
        'updated_by'
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
     * Relación muchos a uno con PurchasePlan
     * Una modificación pertenece a un plan de compra
     */
    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    /**
     * Relación uno a muchos con ModificationHistory
     * Una modificación tiene muchos registros en el historial
     */
    public function history()
    {
        return $this->hasMany(ModificationHistory::class)->orderBy('date', 'desc');
    }

    /**
     * Usuario que creó la modificación
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó la modificación
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Obtiene el siguiente número de modificación para un plan de compra
     *
     * @param int $purchasePlanId
     * @return int
     */
    public static function getNextModificationNumber(int $purchasePlanId): int
    {
        $lastModification = self::where('purchase_plan_id', $purchasePlanId)
            ->orderBy('modification_number', 'desc')
            ->first();

        return $lastModification ? $lastModification->modification_number + 1 : 1;
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
     * Obtiene todos los estados disponibles
     *
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_INACTIVE => 'Inactiva',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada'
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