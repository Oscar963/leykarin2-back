<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupervisorRelationshipRequest;
use App\Http\Resources\SupervisorRelationshipResource;
use App\Models\SupervisorRelationship;
use App\Services\SupervisorRelationshipService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorRelationshipController extends Controller
{
    use LogsActivity;

    private SupervisorRelationshipService $supervisorRelationshipService;

    public function __construct(SupervisorRelationshipService $supervisorRelationshipService)
    {
        $this->supervisorRelationshipService = $supervisorRelationshipService;
    }

    /**
     * Listar todos los tipos de dependencias.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $supervisorRelationships = $this->supervisorRelationshipService->getAllSupervisorRelationshipsByQuery($query, $perPage);

        return SupervisorRelationshipResource::collection($supervisorRelationships)->response();
    }

    /**
     * Guardar un nuevo tipo de relación.
     * @param SupervisorRelationshipRequest $request
     * @return JsonResponse
     */
    public function store(SupervisorRelationshipRequest $request): JsonResponse
    {
        $supervisorRelationship = $this->supervisorRelationshipService->createSupervisorRelationship($request->validated());
        $this->logActivity('create_supervisor_relationship', 'Usuario creó un tipo de dependencia con ID: ' . $supervisorRelationship->id);
        return response()->json([
            'message' => 'Tipo de relación guardado exitosamente',
            'data' => new SupervisorRelationshipResource($supervisorRelationship)
        ], 201);
    }

    /**
     * Mostrar un tipo de relación.
     * @param SupervisorRelationship $supervisorRelationship
     * @return JsonResponse
     */
    public function show(SupervisorRelationship $supervisorRelationship): JsonResponse
    {
        $this->logActivity('show_supervisor_relationship', 'Usuario mostró un tipo de dependencia con ID: ' . $supervisorRelationship->id);
        return response()->json([
            'data' => new SupervisorRelationshipResource($supervisorRelationship)
        ], 200);
    }

    /**
     * Actualizar un tipo de relación.
     * @param SupervisorRelationship $supervisorRelationship
     * @param SupervisorRelationshipRequest $request
     * @return JsonResponse
     */
    public function update(SupervisorRelationship $supervisorRelationship, SupervisorRelationshipRequest $request): JsonResponse
    {
        $updatedSupervisorRelationship = $this->supervisorRelationshipService->updateSupervisorRelationship($supervisorRelationship, $request->validated());
        $this->logActivity('update_supervisor_relationship', 'Usuario actualizó el tipo de relación con ID: ' . $updatedSupervisorRelationship->id);
        return response()->json([
            'message' => 'Tipo de relación actualizado exitosamente',
            'data' => new SupervisorRelationshipResource($updatedSupervisorRelationship)
        ], 200);
    }   

    /**
     * Eliminar un tipo de relación.
     * @param SupervisorRelationship $supervisorRelationship
     * @return JsonResponse
     */
    public function destroy(SupervisorRelationship $supervisorRelationship): JsonResponse
    {
        $this->supervisorRelationshipService->deleteSupervisorRelationship($supervisorRelationship);    
        $this->logActivity('delete_supervisor_relationship', 'Usuario eliminó el tipo de relación con ID: ' . $supervisorRelationship->id);
        return response()->json([
            'message' => 'Tipo de relación eliminado exitosamente'
        ], 200);
    }
}
