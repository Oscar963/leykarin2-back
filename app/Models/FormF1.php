<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormF1 extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'form_f1';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'description',
        'url',
        'type',
        'size',
        'amount',
        'created_by',
        'updated_by'
    ];

    /**
     * Relación 1:1 inversa con PurchasePlan
     * Un formulario F1 pertenece a un plan de compra
     */
    public function purchasePlan()
    {
        return $this->hasOne(PurchasePlan::class, 'form_f1_id');
    }

    /**
     * Usuario que creó el archivo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó el archivo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
