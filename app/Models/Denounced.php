<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denounced extends Model
{
    use HasFactory;

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'complainant_id', 'id');
    }
}
