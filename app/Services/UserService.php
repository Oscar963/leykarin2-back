<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Obtener todos los usuarios.
     */
    public function getAllUsers()
    {
        return User::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Crear un nuevo usuario.
     */
    public function createUser(array $data)
    {
        $user = new User();
        $user->name = $data['name'];
        $user->paternal_surname = $data['paternal_surname'];
        $user->maternal_surname = $data['maternal_surname'];
        $user->rut = $data['rut'];
        $user->email = $data['email'];
        $user->status = $data['status'];
        $user->password = bcrypt($data['password']);;
        $user->save();
        return $user;
    }

    /**
     * Obtener un usuario por ID.
     */
    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Actualizar un usuario existente.
     */
    public function updateUser($id, array $data)
    {
        $user = $this->getUserById($id);

        $user->name = $data['name'];
        $user->paternal_surname = $data['paternal_surname'];
        $user->maternal_surname = $data['maternal_surname'];
        $user->rut = $data['rut'];
        $user->email = $data['email'];
        $user->status = $data['status'];

        $user->save();
        return $user;
    }

    /**
     * Eliminar un usuario.
     */
    public function deleteUser($id)
    {
        $user = $this->getUserById($id);
        $user->save();
        $user->delete();
    }

    /**
     * Restablecer contraseña del usuario.
     */
    public function resetUserPassword(int $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->password = bcrypt($data['password']);
        $user->save();
    }

    /**
     * Actualizar contraseña del usuario.
     */
    public function updateUserPassword(int $id, array $data)
    {
        $user = User::findOrFail($id);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new Exception('La contraseña actual es incorrecta.');
        }

        $user->password = bcrypt($data['new_password']);
        $user->save();
    }
}
