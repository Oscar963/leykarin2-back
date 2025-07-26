<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
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
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $filters = $request->only('rol_avaluo', 'foja');
        $users = $this->userService->getAllUsersByQuery($query, $perPage, $filters);

        $metadata = [
            'ultima_importacion' => '2025-07-25'
        ];

        return UserResource::collection($users)->additional(['meta' => $metadata])->response();
    }

    /**
     * Guardar un nuevo usuario.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        $this->logActivity('create_user', 'Usuario creó un usuario con ID: ' . $user->id);

        return response()->json([
            'message' => 'Usuario guardado exitosamente',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Mostrar un usuario.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * Actualizar un usuario.
     */
    public function update(User $user, UserRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        $this->logActivity('update_user', 'Usuario actualizó el usuario con ID: ' . $updatedUser->id);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => new UserResource($updatedUser)
        ], 200);
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);
        $this->logActivity('delete_user', 'Usuario eliminó el usuario con ID: ' . $user->id);

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ], 200);
    }

    /**
     * Obtener información del usuario autenticado actual.
     */
    public function me(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Cargar roles y permisos
            $user->load(['roles', 'permissions']);

            return response()->json([
                'data' => [
                    'name' => $user->name,
                    'paternal_surname' => $user->paternal_surname,
                    'maternal_surname' => $user->maternal_surname,
                    'rut' => $user->rut,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
