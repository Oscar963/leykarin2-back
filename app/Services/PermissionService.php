<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionService
{
    /**
     * Obtiene todos los roles ordenados por fecha de creación (descendente).
     * @return Collection
     */
    public function getAllPermissions()
    {
        return Permission::latest()->get();
    }

    /**
     * Obtiene todos los permisos con filtros y paginación.
     * @param ?string $query
     * @param ?int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPermissionsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Permission::oldest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('guard_name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo permiso usando asignación masiva.
     * @param array $data
     * @return Permission
     */
    public function createPermission(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Obtiene un permiso por su ID.
     * @param int $id
     * @return Permission
     */
    public function getPermissionById(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    /**
     * Actualiza un permiso usando asignación masiva.
     * @param Permission $permission
     * @param array $data
     * @return Permission
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission;
    }

    /**
     * Elimina un permiso.
     * @param Permission $permission
     * @return Permission
     */
    public function deletePermission(Permission $permission): Permission
    {
        $permission->delete();
        return $permission;
    }
}
