<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;

class UserController extends Controller
{
    use LogsActivity;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Listar todos los usuarios.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');
            $banners = $this->userService->getAllUsersByQuery($query, $perPage);
            return response()->json(['data' =>  UserResource::collection($banners)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los usuarios: '], 500);
        }
    }

    /**
     * Guardar un nuevo usuario.
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
            $this->logActivity('create_user', 'Usuario creó un usuario con ID: ' . $user->id);

            return response()->json(['message' => 'Usuario guardado exitosamente', 'data' => new UserResource($user)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar el usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar un usuario específico.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            return response()->json(['data' => new UserResource($user)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
    }

    /**
     * Actualizar un usuario.
     */
    public function update(int $id, UserRequest $request): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($id, $request->validated());
            $this->logActivity('update_user', 'Usuario actualizó el usuario con ID: ' . $updatedUser->id);

            return response()->json(['message' => 'Usuario actualizado exitosamente', 'data' => new UserResource($updatedUser)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            $this->logActivity('delete_user', 'Usuario eliminó el usuario con ID: ' . $id);
            return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restablecer la contraseña de un usuario.
     */
    public function resetPassword(int $id, ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->userService->resetUserPassword($id, $request->validated());
            $this->logActivity('reset_password', 'Usuario restableció la contraseña del usuario con ID: ', $id);

            return response()->json(['message' => 'Contraseña restablecida exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al restablecer la contraseña: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar la contraseña del usuario autenticado.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $this->userService->updateUserPassword($userId, $request->validated());
            $this->logActivity('update_password', 'Usuario actualizó su contraseña.', $userId);

            return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar la contraseña: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar la contraseña del usuario autenticado.
     */
    public function profile(): JsonResponse
    {
        try {
            $user = auth()->user();
            $this->logActivity('view_profile', 'Usuario consultó su perfil.', $user->id);
            return response()->json(['data' => new UserResource($user)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener el perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un perfil.
     */
    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateProfile($request->validated());
            $this->logActivity('update_user', 'Usuario actualizó su perfil ID: ' . $updatedUser->id);

            return response()->json(['message' => 'Perfil actualizado exitosamente', 'data' => new UserResource($updatedUser)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el perfil: ' . $e->getMessage()], 500);
        }
    }
}
