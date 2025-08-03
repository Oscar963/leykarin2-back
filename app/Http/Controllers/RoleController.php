<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Http\Resources\RoleResource;
use App\Models\Role;

class RoleController extends Controller
{
    use LogsActivity;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Listar todos los inmuebles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $filters = $request->only('rol_avaluo', 'foja');
        $roles = $this->roleService->getAllRolesByQuery($query, $perPage, $filters);

        return RoleResource::collection($roles)->response();
    }

    /**
     * Guardar un nuevo role.
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole($request->validated());

        $this->logActivity('create_role', 'Usuario creó un role con ID: ' . $role->id);
        return response()->json([
            'message' => 'Role guardado exitosamente',
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * Mostrar un role.
     */
    public function show(Role $role): JsonResponse
    {
        $this->logActivity('show_role', 'Usuario mostró un role con ID: ' . $role->id);
        return response()->json([
            'data' => new RoleResource($role)
        ], 200);
    }

    /**
     * Actualizar un role.
     */
    public function update(Role $role, RoleRequest $request): JsonResponse
    {
        $updatedRole = $this->roleService->updateRole($role, $request->validated());

        $this->logActivity('update_role', 'Usuario actualizó el role con ID: ' . $updatedRole->id);
        return response()->json([
            'message' => 'Role actualizado exitosamente',
            'data' => new RoleResource($updatedRole)
        ], 200);
    }

    /**
     * Eliminar un role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->deleteRole($role);

        $this->logActivity('delete_role', 'Usuario eliminó el role con ID: ' . $role->id);
        return response()->json([
            'message' => 'Role eliminado exitosamente'
        ], 200);
    }
}
