<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
