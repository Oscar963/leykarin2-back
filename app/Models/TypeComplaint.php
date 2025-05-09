<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeComplaint extends Model
{
    use HasFactory;

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'type_complaint_id');
    }
}
