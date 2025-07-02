<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModificationRequest;
use App\Http\Resources\ModificationResource;
use App\Services\ModificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModificationController extends Controller
{
    protected $modificationService;

    public function __construct(ModificationService $modificationService)
    {
        $this->modificationService = $modificationService;
    }

    /**
     * Obtiene todas las modificaciones con paginación y filtrado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query');
        $perPage = $request->get('per_page', 15);

        $modifications = $this->modificationService->getAllModificationsByQuery($query, $perPage);

        return response()->json([
            'success' => true,
            'data' => ModificationResource::collection($modifications),
            'pagination' => [
                'current_page' => $modifications->currentPage(),
                'last_page' => $modifications->lastPage(),
                'per_page' => $modifications->perPage(),
                'total' => $modifications->total(),
            ]
        ]);
    }

    /**
     * Obtiene modificaciones por plan de compra
     *
     * @param int $purchasePlanId
     * @return JsonResponse
     */
    public function getByPurchasePlan(int $purchasePlanId): JsonResponse
    {
        $modifications = $this->modificationService->getModificationsByPurchasePlan($purchasePlanId);
        $stats = $this->modificationService->getModificationStats($purchasePlanId);

        return response()->json([
            'success' => true,
            'data' => ModificationResource::collection($modifications),
            'stats' => $stats
        ]);
    }

    /**
     * Obtiene una modificación específica
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $modification = $this->modificationService->getModificationById($id);

        return response()->json([
            'success' => true,
            'data' => new ModificationResource($modification)
        ]);
    }

    /**
     * Crea una nueva modificación
     *
     * @param ModificationRequest $request
     * @return JsonResponse
     */
    public function store(ModificationRequest $request): JsonResponse
    {
        $modification = $this->modificationService->createModification($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Modificación creada exitosamente',
            'data' => new ModificationResource($modification)
        ], 201);
    }

    /**
     * Actualiza una modificación existente
     *
     * @param ModificationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ModificationRequest $request, int $id): JsonResponse
    {
        $modification = $this->modificationService->updateModification($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Modificación actualizada exitosamente',
            'data' => new ModificationResource($modification)
        ]);
    }

    /**
     * Cambia el estado de una modificación
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,pending,approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        $modification = $this->modificationService->changeModificationStatus(
            $id,
            $request->status,
            $request->comment
        );

        return response()->json([
            'success' => true,
            'message' => 'Estado de la modificación actualizado exitosamente',
            'data' => new ModificationResource($modification)
        ]);
    }

    /**
     * Elimina una modificación
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->modificationService->deleteModification($id);

        return response()->json([
            'success' => true,
            'message' => 'Modificación eliminada exitosamente'
        ]);
    }

    /**
     * Obtiene los estados disponibles para las modificaciones
     *
     * @return JsonResponse
     */
    public function getAvailableStatuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Modification::getAvailableStatuses()
        ]);
    }
} 