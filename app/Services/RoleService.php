<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    /**
     * Obtiene todos los roles ordenados por fecha de creación (descendente).
     * @return Collection<Role>
     */
    public function getAllRoles()
    {
        return Role::latest()->get();
    }

    /**
     * Obtiene todos los roles con filtros y paginación.
     * @param ?string $query
     * @param ?int $perPage
     * @return LengthAwarePaginator<Role>   
     */
    public function getAllRolesByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Role::with('permissions')
            ->oldest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('guard_name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo role usando asignación masiva.
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role
    {
        $data['guard_name'] = 'web';
        $role = Role::create($data);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Obtiene un role por su ID.
     * @param int $id
     * @return Role
     */
    public function getRoleById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    /**
     * Actualiza un role usando asignación masiva.
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role
    {
        $data['guard_name'] = 'web';
        $role->update($data);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Elimina un role.
     * @param Role $role
     * @return Role
     */
    public function deleteRole(Role $role): Role
    {
        $role->delete();
        return $role;
    }
}
