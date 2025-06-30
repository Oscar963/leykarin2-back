<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirectionRequest;
use App\Http\Resources\DirectionResource;
use App\Services\DirectionService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Resources\UserResource;
use App\Models\Direction;
use App\Models\User;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    use LogsActivity;

    protected $directionService;

    public function __construct(DirectionService $directionService)
    {
        $this->directionService = $directionService;
    }

    public function index(): JsonResponse
    {
        try {
            $directions = $this->directionService->getAllDirections();

            return response()->json([
                'data' => DirectionResource::collection($directions)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las direcciones.'
            ], 500);
        }
    }

    public function store(DirectionRequest $request): JsonResponse
    {
        try {
            $direction = $this->directionService->createDirection($request->validated());
            $this->logActivity('create_direction', 'Usuario creó una dirección con ID: ' . $direction->id);

            return response()->json([
                'message' => 'Dirección ha sido creada exitosamente',
                'data' => new DirectionResource($direction)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear la dirección. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $direction = $this->directionService->getDirectionById($id);

            return response()->json([
                'data' => new DirectionResource($direction)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Dirección no encontrada.'
            ], 404);
        }
    }

    public function update(int $id, DirectionRequest $request): JsonResponse
    {
        try {
            $direction = $this->directionService->updateDirection($id, $request->validated());
            $this->logActivity('update_direction', 'Usuario actualizó la dirección con ID: ' . $direction->id);

            return response()->json([
                'message' => 'Dirección ha sido actualizada exitosamente',
                'data' => new DirectionResource($direction)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la dirección. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->directionService->deleteDirection($id);
            $this->logActivity('delete_direction', 'Usuario eliminó la dirección con ID: ' . $id);

            return response()->json([
                'message' => 'Dirección ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la dirección. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el director de una dirección específica
     */
    public function getDirector(Direction $direction)
    {
        $director = $direction->director;

        if (!$director) {
            return response()->json([
                'message' => 'Esta dirección no tiene director asignado'
            ], 404);
        }

        return response()->json([
            'director' => new UserResource($director)
        ]);
    }

    /**
     * Obtiene todos los usuarios de una dirección específica
     */
    public function getUsers(Direction $direction)
    {
        $users = $direction->users()->with('roles')->get();

        return response()->json([
            'direction' => new DirectionResource($direction),
            'users' => UserResource::collection($users)
        ]);
    }

    /**
     * Obtiene usuarios de una dirección por rol específico
     */
    public function getUsersByRole(Direction $direction, Request $request)
    {
        $request->validate([
            'role' => 'required|string'
        ]);

        $users = $direction->getUsersByRole($request->role);

        return response()->json([
            'direction' => new DirectionResource($direction),
            'role' => $request->role,
            'users' => UserResource::collection($users)
        ]);
    }

    /**
     * Asigna un director a una dirección
     */
    public function assignDirector(Request $request, Direction $direction)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Verificar que el usuario tenga el rol de Director
        if (!$user->hasRole('Director')) {
            return response()->json([
                'message' => 'El usuario debe tener el rol de Director'
            ], 400);
        }

        // Actualizar el director_id de la dirección
        $direction->update(['director_id' => $user->id]);

        // Asegurar que el usuario esté relacionado con la dirección
        $direction->users()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'message' => 'Director asignado exitosamente',
            'direction' => new DirectionResource($direction),
            'director' => new UserResource($user)
        ]);
    }

    /**
     * Asigna usuarios a una dirección
     */
    public function assignUsers(Request $request, Direction $direction)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();

        // Asignar usuarios a la dirección
        $direction->users()->syncWithoutDetaching($request->user_ids);

        return response()->json([
            'message' => 'Usuarios asignados exitosamente',
            'direction' => new DirectionResource($direction),
            'assigned_users' => UserResource::collection($users)
        ]);
    }

    /**
     * Remueve usuarios de una dirección
     */
    public function removeUsers(Request $request, Direction $direction)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        // No permitir remover al director de su dirección
        $usersToRemove = array_filter($request->user_ids, function ($userId) use ($direction) {
            return $userId != $direction->director_id;
        });

        if (empty($usersToRemove)) {
            return response()->json([
                'message' => 'No se puede remover al director de su dirección'
            ], 400);
        }

        $direction->users()->detach($usersToRemove);

        return response()->json([
            'message' => 'Usuarios removidos exitosamente',
            'direction' => new DirectionResource($direction)
        ]);
    }

    /**
     * Obtiene estadísticas de usuarios por dirección
     */
    public function getUserStats()
    {
        $directions = Direction::with(['director', 'users.roles'])->get();

        $stats = $directions->map(function ($direction) {
            $usersByRole = $direction->users->groupBy(function ($user) {
                return $user->roles->first() ? $user->roles->first()->name : 'Sin rol';
            });

            return [
                'direction' => new DirectionResource($direction),
                'total_users' => $direction->users->count(),
                'has_director' => !is_null($direction->director),
                'users_by_role' => $usersByRole->map(function ($users) {
                    return UserResource::collection($users);
                })
            ];
        });

        return response()->json([
            'stats' => $stats
        ]);
    }
}
