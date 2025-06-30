<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicationMonth extends Model
{
    protected $fillable = ['name', 'short_name', 'month_number', 'year'];

    /**
     * Relación con ItemPurchase
     */
    public function itemPurchases(): HasMany
    {
        return $this->hasMany(ItemPurchase::class);
    }

    /**
     * Scope para obtener meses activos ordenados
     */
    public function scopeActive($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('month_number');
    }

    /**
     * Scope para obtener meses por año
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Obtener el nombre completo del mes
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Obtener el nombre corto del mes (sin conflictos)
     */
    public function getShortNameFormattedAttribute(): string
    {
        return $this->short_name;
    }

    /**
     * Obtener el formato completo "Dic 2025"
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->short_name . ' ' . $this->year;
    }
}
