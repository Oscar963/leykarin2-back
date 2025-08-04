<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;


class PermissionController extends Controller
{
    use LogsActivity;

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Listar todos los permissions.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $permissions = $this->permissionService->getAllPermissionsByQuery($query, $perPage);

        return PermissionResource::collection($permissions)->response();
    }

    /**
     * Guardar un nuevo permission.
     * @param PermissionRequest $request
     * @return JsonResponse
     */
    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->createPermission($request->validated());

        $this->logActivity('create_permission', 'Usuario creó un permission con ID: ' . $permission->id);
        return response()->json([
            'message' => 'Permission guardado exitosamente',
            'data' => new PermissionResource($permission)
        ], 201);
    }

    /**
     * Mostrar un permission.
     * @param Permission $permission
     * @return JsonResponse
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->logActivity('show_permission', 'Usuario mostró un permission con ID: ' . $permission->id);
        return response()->json([
            'data' => new PermissionResource($permission)
        ], 200);
    }

    /**
     * Actualizar un permission.
     * @param Permission $permission
     * @param PermissionRequest $request
     * @return JsonResponse
     */
    public function update(Permission $permission, PermissionRequest $request): JsonResponse
    {
        $updatedPermission = $this->permissionService->updatePermission($permission, $request->validated());

        $this->logActivity('update_permission', 'Usuario actualizó el permission con ID: ' . $updatedPermission->id);
        return response()->json([
            'message' => 'Permission actualizado exitosamente',
            'data' => new PermissionResource($updatedPermission)
        ], 200);
    }

    /**
     * Eliminar un permission.
     * @param Permission $permission
     * @return JsonResponse 
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->permissionService->deletePermission($permission);

        $this->logActivity('delete_permission', 'Usuario eliminó el permission con ID: ' . $permission->id);
        return response()->json([
            'message' => 'Permission eliminado exitosamente'
        ], 200);
    }
}
