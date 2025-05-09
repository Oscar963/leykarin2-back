<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function complainants()
    {
        return $this->hasMany(Complainant::class, 'dependence_id');
    }
}
