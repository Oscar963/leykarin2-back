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

    /**
     * Obtiene todos los usuarios de esta dirección que tienen un rol específico
     */
    public function getUsersByRole(string $roleName)
    {
        return $this->users()->whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }

    /**
     * Obtiene el director de esta dirección
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Obtiene todos los usuarios que no son director de esta dirección
     */
    public function getNonDirectorUsers()
    {
        return $this->users()->where('id', '!=', $this->director_id)->get();
    }

    /**
     * Verifica si un usuario pertenece a esta dirección
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Obtiene el número total de usuarios en esta dirección
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }
}
