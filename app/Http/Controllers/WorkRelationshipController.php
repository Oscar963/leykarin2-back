<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkRelationshipRequest;
use App\Http\Resources\WorkRelationshipResource;
use App\Models\WorkRelationship;
use App\Services\WorkRelationshipService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkRelationshipController extends Controller
{
    use LogsActivity;

    private WorkRelationshipService $workRelationshipService;

    public function __construct(WorkRelationshipService $workRelationshipService)
    {
        $this->workRelationshipService = $workRelationshipService;
    }

    /**
     * Listar todos los tipos de relacion laboral.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $workRelationships = $this->workRelationshipService->getAllWorkRelationshipsByQuery($query, $perPage);

        return WorkRelationshipResource::collection($workRelationships)->response();
    }

    /**
     * Guardar un nuevo tipo de relacion laboral.
     * @param WorkRelationshipRequest $request
     * @return JsonResponse
     */
    public function store(WorkRelationshipRequest $request): JsonResponse
    {
        $workRelationship = $this->workRelationshipService->createWorkRelationship($request->validated());
        $this->logActivity('create_work_relationship', 'Usuario creó un tipo de relacion laboral con ID: ' . $workRelationship->id);
        return response()->json([
            'message' => 'Tipo de relacion laboral guardado exitosamente',
            'data' => new WorkRelationshipResource($workRelationship)
        ], 201);
    }

    /**
     * Mostrar un tipo de relacion laboral.
     * @param WorkRelationship $workRelationship
     * @return JsonResponse
     */
    public function show(WorkRelationship $workRelationship): JsonResponse
    {
        $this->logActivity('show_work_relationship', 'Usuario mostró un tipo de relacion laboral con ID: ' . $workRelationship->id);
        return response()->json([
            'data' => new WorkRelationshipResource($workRelationship)
        ], 200);
    }

    /**
     * Actualizar un tipo de relacion laboral.
     * @param WorkRelationship $workRelationship
     * @param WorkRelationshipRequest $request
     * @return JsonResponse
     */
    public function update(WorkRelationship $workRelationship, WorkRelationshipRequest $request): JsonResponse
    {
        $updatedWorkRelationship = $this->workRelationshipService->updateWorkRelationship($workRelationship, $request->validated());
        $this->logActivity('update_work_relationship', 'Usuario actualizó el tipo de relacion laboral con ID: ' . $updatedWorkRelationship->id);
        return response()->json([
            'message' => 'Tipo de relacion laboral actualizado exitosamente',
            'data' => new WorkRelationshipResource($updatedWorkRelationship)
        ], 200);
    }

    /**
     * Eliminar un tipo de relacion laboral.
     * @param WorkRelationship $workRelationship
     * @return JsonResponse
     */
    public function destroy(WorkRelationship $workRelationship): JsonResponse
    {
        $this->workRelationshipService->deleteWorkRelationship($workRelationship);
        $this->logActivity('delete_work_relationship', 'Usuario eliminó el tipo de relacion laboral con ID: ' . $workRelationship->id);
        return response()->json([
            'message' => 'Tipo de relacion laboral eliminado exitosamente'
        ], 200);
    }
}
