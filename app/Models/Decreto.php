<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decreto extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'decretos';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'description',
        'url',
        'type',
        'size',
        'extension',
        'created_by',
        'updated_by'
    ];

    /**
     * Relación 1:1 inversa con PurchasePlan
     * Un decreto pertenece a un plan de compra
     */
    public function purchasePlan()
    {
        return $this->hasOne(PurchasePlan::class, 'decreto_id');
    }

    /**
     * Usuario que creó el decreto
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó el decreto
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
} 