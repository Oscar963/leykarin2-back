<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    /**
     * Roles que deben pertenecer únicamente a una dirección
     * NOTA: Los administradores y secretaría comunal de planificación pueden tener múltiples direcciones
     */
    const HIERARCHICAL_ROLES = [
        'Director',
        'Subrogante de Director',
        'Jefatura',
        'Subrogante de Jefatura'
    ];

    /**
     * Roles que pueden tener múltiples direcciones (excluidos de la regla de dirección única)
     */
    const MULTI_DIRECTION_ROLES = [
        'Administrador del Sistema',
        'Administrador Municipal',
        'Encargado de Presupuestos',
        'Subrogante de Encargado de Presupuestos'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'paternal_surname',
        'maternal_surname',
        'rut',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function directions()
    {
        return $this->belongsToMany(Direction::class);
    }

    /**
     * Obtiene la dirección principal del usuario (la primera asignada)
     */
    public function getMainDirection()
    {
        return $this->directions()->first();
    }

    /**
     * Verifica si el usuario tiene roles jerárquicos
     */
    public function hasHierarchicalRole(): bool
    {
        return $this->hasAnyRole(self::HIERARCHICAL_ROLES);
    }

    /**
     * Obtiene los roles jerárquicos del usuario
     */
    public function getHierarchicalRoles()
    {
        return $this->getRoleNames()->intersect(self::HIERARCHICAL_ROLES);
    }

    /**
     * Verifica si el usuario puede pertenecer a múltiples direcciones
     */
    public function canBelongToMultipleDirections(): bool
    {
        // Los usuarios con roles de administrador o secretaría comunal pueden tener múltiples direcciones
        if ($this->hasAnyRole(self::MULTI_DIRECTION_ROLES)) {
            return true;
        }

        // Los usuarios con roles jerárquicos solo pueden tener una dirección
        return !$this->hasHierarchicalRole();
    }

    /**
     * Valida que el usuario cumpla con las reglas de dirección única para roles jerárquicos
     */
    public function validateDirectionAssignment(): bool
    {
        // Los usuarios con roles de administrador o secretaría comunal pueden tener múltiples direcciones
        if ($this->hasAnyRole(self::MULTI_DIRECTION_ROLES)) {
            return true;
        }

        // Los usuarios con roles jerárquicos solo pueden tener una dirección
        if ($this->hasHierarchicalRole()) {
            return $this->directions()->count() <= 1;
        }

        // Otros usuarios pueden tener múltiples direcciones
        return true;
    }

    /**
     * Asigna una dirección al usuario, validando las reglas de negocio
     */
    public function assignDirection(Direction $direction): bool
    {
        // Si el usuario tiene roles jerárquicos y no es administrador/secretaría, reemplazar direcciones
        if ($this->hasHierarchicalRole() && !$this->hasAnyRole(self::MULTI_DIRECTION_ROLES)) {
            $this->directions()->detach();
        }

        $this->directions()->attach($direction->id);
        return true;
    }

    /**
     * Asigna múltiples direcciones al usuario, validando las reglas de negocio
     */
    public function assignDirections(array $directionIds): bool
    {
        // Verificar si el usuario puede tener múltiples direcciones
        if (!$this->canBelongToMultipleDirections()) {
            if (count($directionIds) > 1) {
                throw new \InvalidArgumentException(
                    'Los usuarios con roles jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura) solo pueden pertenecer a una dirección. Los administradores y secretaría comunal de planificación pueden tener múltiples direcciones.'
                );
            }
        }

        $this->directions()->sync($directionIds);
        return true;
    }

    /**
     * Obtiene el director de la dirección principal del usuario
     */
    public function getDirectionDirector()
    {
        $mainDirection = $this->getMainDirection();
        return $mainDirection ? $mainDirection->director : null;
    }

    /**
     * Verifica si el usuario es director de su dirección principal
     */
    public function isDirectorOfMainDirection(): bool
    {
        $mainDirection = $this->getMainDirection();
        return $mainDirection && $mainDirection->director_id === $this->id;
    }
}
