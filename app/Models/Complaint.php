<?php

namespace App\Models;

use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory, HasFiles;
    
    protected $fillable = [
        'folio',
        'token',
        'type_complaint_id',
        'complainant_id',
        'denounced_id',
        'hierarchical_level_id',
        'work_relationship_id',
        'supervisor_relationship_id',
        'circumstances_narrative',
        'consequences_narrative',
    ];

    public function typeComplaint()
    {
        return $this->belongsTo(TypeComplaint::class);
    }

    public function complainant()
    {
        return $this->belongsTo(Complainant::class);
    }

    public function denounced()
    {
        return $this->belongsTo(Denounced::class);
    }

    public function hierarchicalLevel()
    {
        return $this->belongsTo(HierarchicalLevel::class);
    }

    public function workRelationship()
    {
        return $this->belongsTo(WorkRelationship::class);
    }

    public function supervisorRelationship()
    {
        return $this->belongsTo(SupervisorRelationship::class);
    }

    public function witnesses()
    {
        return $this->hasMany(Witness::class);
    }

    /**
     * Relaciones estándar para carga completa de denuncias
     */
    public static function getStandardRelations(): array
    {
        return [
            'complainant.typeDependency',
            'denounced',
            'typeComplaint',
            'hierarchicalLevel',
            'workRelationship',
            'supervisorRelationship',
            'witnesses',
        ];
    }
}
