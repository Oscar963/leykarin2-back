<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    /**
     * Obtiene todos los roles ordenados por fecha de creación (descendente).
     */
    public function getAllRoles()
    {
        return Role::latest()->get();
    }

    /**
     * Obtiene todos los roles con filtros y paginación.
     */
    public function getAllRolesByQuery(?string $query, ?int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        return Role::oldest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('guard_name', 'LIKE', "%{$query}%");
            })
            ->when(!empty($filters), function (Builder $q) use ($filters) {
                $q->where($filters);
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo role usando asignación masiva.
     */
    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Obtiene un role por su ID.
     */
    public function getRoleById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    /**
     * Actualiza un role usando asignación masiva.
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    /**
     * Elimina un role.
     */
    public function deleteRole(Role $role): Role
    {
        $role->delete();
        return $role;
    }
}
