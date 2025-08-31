<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complainant extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_dependency_id',
        'name',
        'address',
        'rut',
        'phone',
        'charge',
        'email',
        'unit',
        'function',
        'grade',
        'birthdate',
        'entry_date',
        'contractual_status',
        'is_victim',
    ];

    public function typeDependency()
    {
        return $this->belongsTo(TypeDependency::class);
    }

    public function complainant()
    {
        return $this->belongsTo(Complainant::class);
    }
}
