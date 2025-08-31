<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denounced extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'rut',
        'phone',
        'charge',
        'email',
        'unit',
        'function',
        'grade',
    ];

    public function complainant()
    {
        return $this->belongsTo(Complainant::class);
    }
}
