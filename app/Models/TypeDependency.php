<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDependency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email_notification',
    ];
}
