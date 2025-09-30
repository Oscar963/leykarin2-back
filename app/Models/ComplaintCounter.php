<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_dependency_id',
        'year',
        'current_seq',
    ];

    protected $casts = [
        'year' => 'integer',
        'current_seq' => 'integer',
    ];

    /**
     * Relación con el tipo de dependencia.
     */
    public function typeDependency()
    {
        return $this->belongsTo(TypeDependency::class, 'type_dependency_id');
    }

    /**
     * Obtiene o crea un contador para el año y dependencia especificados.
     *
     * @param int $typeDependencyId
     * @param int $year
     * @return ComplaintCounter
     */
    public static function getOrCreateCounter(int $typeDependencyId, int $year): self
    {
        return static::firstOrCreate(
            [
                'type_dependency_id' => $typeDependencyId,
                'year' => $year,
            ],
            [
                'current_seq' => 0,
            ]
        );
    }

    /**
     * Incrementa el contador y retorna el nuevo valor.
     *
     * @return int
     */
    public function incrementAndGet(): int
    {
        $this->increment('current_seq');
        return $this->fresh()->current_seq;
    }
}
