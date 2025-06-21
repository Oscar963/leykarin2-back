<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alias',
        'director_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function purchasePlans()
    {
        return $this->hasMany(PurchasePlan::class);
    }
}
