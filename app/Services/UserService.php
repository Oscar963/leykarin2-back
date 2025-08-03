<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Obtiene todos los usuarios ordenados por fecha de creación (descendente).
     */
    public function getAllUsers()
    {
        return User::latest()->get();
    }

    /**
     * Obtiene todos los usuarios con filtros y paginación.
     */
    public function getAllUsersByQuery(?string $query, ?int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        return User::with('roles')
            ->oldest('id')
            ->where('rut', '!=', '185995380') // Excluir al administrador del sistema principal
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('paternal_surname', 'LIKE', "%{$query}%")
                    ->orWhere('maternal_surname', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('rut', 'LIKE', "%{$query}%");
            })
            ->when(!empty($filters), function (Builder $q) use ($filters) {
                $q->where($filters);
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo usuario usando asignación masiva.
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Obtiene un usuario por su ID.
     */
    public function getUserById(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Actualiza un usuario usando asignación masiva.
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        return $user;
    }

    /**
     * Elimina un usuario.
     */
    public function deleteUser(User $user): User
    {
        $user->delete();
        return $user;
    }
}
