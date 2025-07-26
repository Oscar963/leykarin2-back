<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserServiceCopy
{
    /**
     * Obtiene todos los usuarios
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsers()
    {
        return User::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene usuarios paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUsersByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = User::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('rut', 'LIKE', "%{$query}%")
                    ->orWhere('name', 'LIKE', "%{$query}%")
                    ->orWhere('paternal_surname', 'LIKE', "%{$query}%")
                    ->orWhere('maternal_surname', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un usuario por su ID
     *
     * @param int $id ID del usuario
     * @return User
     */
    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Crea un nuevo usuario
     *
     * @param array $data Datos del usuario
     * @return User
     */
    public function createUser(array $data)
    {
        $user = new User();
        $user->name = trim($data['name']);
        $user->paternal_surname = trim($data['paternal_surname']);
        $user->maternal_surname = trim($data['maternal_surname']);
        $user->rut = trim($data['rut']);
        $user->email = trim($data['email']);
        $user->status = $data['status'];
        $user->password = bcrypt($data['password']);
        $user->save();

        return $user;
    }

    /**
     * Actualiza un usuario existente
     *
     * @param int $id ID del usuario
     * @param array $data Datos actualizados
     * @return User
     */
    public function updateUser($id, array $data)
    {
        $user = $this->getUserById($id);
        $user->name = trim($data['name']);
        $user->paternal_surname = trim($data['paternal_surname']);
        $user->maternal_surname = trim($data['maternal_surname']);
        $user->rut = trim($data['rut']);
        $user->email = trim($data['email']);
        $user->status = $data['status'];
        $user->save();

        return $user;
    }

    /**
     * Elimina un usuario
     *
     * @param int $id ID del usuario
     * @return void
     */
    public function deleteUser($id)
    {
        $user = $this->getUserById($id);
        $user->delete();
    }

    /**
     * Restablece la contraseña de un usuario
     *
     * @param int $id ID del usuario
     * @param array $data Datos de la contraseña
     * @return void
     */
    public function resetUserPassword(int $id, array $data)
    {
        $user = $this->getUserById($id);
        $user->password = bcrypt($data['password']);
        $user->save();
    }

    /**
     * Actualiza la contraseña de un usuario
     *
     * @param int $id ID del usuario
     * @param array $data Datos de la contraseña
     * @throws Exception Si la contraseña actual es incorrecta
     * @return void
     */
    public function updateUserPassword(int $id, array $data)
    {
        $user = $this->getUserById($id);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new Exception('La contraseña actual es incorrecta.');
        }

        $user->password = bcrypt($data['new_password']);
        $user->save();
    }

    /**
     * Actualiza el perfil del usuario autenticado
     *
     * @param array $data Datos del perfil
     * @return User
     */
    public function updateProfile(array $data)
    {
        $user = $this->getUserById(auth()->id());
        $user->name = trim($data['name']);
        $user->paternal_surname = trim($data['paternal_surname']);
        $user->maternal_surname = trim($data['maternal_surname']);
        $user->rut = trim($data['rut']);
        $user->email = trim($data['email']);
        $user->save();

        return $user;
    }
}
