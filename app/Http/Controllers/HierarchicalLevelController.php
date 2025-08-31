<?php

namespace App\Http\Controllers;

use App\Http\Requests\HierarchicalLevelRequest;
use App\Http\Resources\HierarchicalLevelResource;
use App\Models\HierarchicalLevel;
use App\Services\HierarchicalLevelService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HierarchicalLevelController extends Controller
{
    use LogsActivity;

    private HierarchicalLevelService $hierarchicalLevelService;

    public function __construct(HierarchicalLevelService $hierarchicalLevelService)
    {
        $this->hierarchicalLevelService = $hierarchicalLevelService;
    }

    /**
     * Listar todos los niveles jerárquicos.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $hierarchicalLevels = $this->hierarchicalLevelService->getAllHierarchicalLevelsByQuery($query, $perPage);

        return HierarchicalLevelResource::collection($hierarchicalLevels)->response();
    }

    /**
     * Guardar un nuevo nivel jerárquico.
     * @param HierarchicalLevelRequest $request
     * @return JsonResponse
     */
    public function store(HierarchicalLevelRequest $request): JsonResponse
    {
        $hierarchicalLevel = $this->hierarchicalLevelService->createHierarchicalLevel($request->validated());
        $this->logActivity('create_hierarchical_level', 'Usuario creó un nivel jerárquico con ID: ' . $hierarchicalLevel->id);
        return response()->json([
            'message' => 'Nivel jerárquico guardado exitosamente',
            'data' => new HierarchicalLevelResource($hierarchicalLevel)
        ], 201);
    }

    /**
     * Mostrar un nivel jerárquico.
     * @param HierarchicalLevel $hierarchicalLevel
     * @return JsonResponse
     */
    public function show(HierarchicalLevel $hierarchicalLevel): JsonResponse
    {
        $this->logActivity('show_hierarchical_level', 'Usuario mostró un nivel jerárquico con ID: ' . $hierarchicalLevel->id);
        return response()->json([
            'data' => new HierarchicalLevelResource($hierarchicalLevel)
        ], 200);
    }

    /**
     * Actualizar un nivel jerárquico.
     * @param HierarchicalLevel $hierarchicalLevel
     * @param HierarchicalLevelRequest $request
     * @return JsonResponse
     */
    public function update(HierarchicalLevel $hierarchicalLevel, HierarchicalLevelRequest $request): JsonResponse
    {
        $updatedHierarchicalLevel = $this->hierarchicalLevelService->updateHierarchicalLevel($hierarchicalLevel, $request->validated());
        $this->logActivity('update_hierarchical_level', 'Usuario actualizó el nivel jerárquico con ID: ' . $updatedHierarchicalLevel->id);
        return response()->json([
            'message' => 'Nivel jerárquico actualizado exitosamente',
            'data' => new HierarchicalLevelResource($updatedHierarchicalLevel)
        ], 200);
    }

    /**
     * Eliminar un nivel jerárquico.
     * @param HierarchicalLevel $hierarchicalLevel
     * @return JsonResponse
     */
    public function destroy(HierarchicalLevel $hierarchicalLevel): JsonResponse
    {
        $this->hierarchicalLevelService->deleteHierarchicalLevel($hierarchicalLevel);    
        $this->logActivity('delete_hierarchical_level', 'Usuario eliminó el nivel jerárquico con ID: ' . $hierarchicalLevel->id);
        return response()->json([
            'message' => 'Nivel jerárquico eliminado exitosamente'
        ], 200);
    }
}
