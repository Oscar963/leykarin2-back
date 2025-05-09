<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    public function complainant()
    {
        return $this->belongsTo(Complainant::class, 'complainant_id', 'id');
    }

    public function denounced()
    {
        return $this->belongsTo(Denounced::class, 'denounced_id', 'id');
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class, 'complaint_id', 'id');
    }

    public function witnesses()
    {
        return $this->hasMany(Witness::class);
    }

    public function typeComplaint()
    {
        return $this->belongsTo(TypeComplaint::class, 'type_complaint_id');
    }
}
