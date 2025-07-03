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
        $status = $request->get('status');
        $modificationTypeId = $request->get('modification_type_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $modifications = $this->modificationService->getAllModificationsByQuery(
            $query, 
            $perPage, 
            $status, 
            $modificationTypeId, 
            $startDate, 
            $endDate
        );

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
            'status' => 'required|string|in:active,inactive,pending,approved,rejected'
        ]);

        $modification = $this->modificationService->changeModificationStatus($id, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la modificación actualizado exitosamente',
            'data' => new ModificationResource($modification)
        ]);
    }

    /**
     * Obtiene modificaciones por plan de compra
     *
     * @param int $purchasePlanId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByPurchasePlan(int $purchasePlanId, Request $request): JsonResponse
    {
        $query = $request->get('query');
        $perPage = $request->get('per_page', 15);
        
        $modifications = $this->modificationService->getModificationsByPurchasePlan($purchasePlanId, $query, $perPage);

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
     * Obtiene modificaciones pendientes de aprobación
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPendingApproval(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $modifications = $this->modificationService->getPendingApprovalModifications($perPage);

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
     * Obtiene los estados disponibles
     *
     * @return JsonResponse
     */
    public function getAvailableStatuses(): JsonResponse
    {
        $statuses = $this->modificationService->getAvailableStatuses();

        return response()->json([
            'success' => true,
            'data' => $statuses
        ]);
    }

    /**
     * Obtiene los tipos de modificación disponibles
     *
     * @return JsonResponse
     */
    public function getAvailableTypes(): JsonResponse
    {
        $types = $this->modificationService->getAvailableTypes();

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Obtiene estadísticas básicas de modificaciones
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        $basicStats = $this->modificationService->getBasicStatistics();
        $statsByType = $this->modificationService->getStatisticsByType();

        return response()->json([
            'success' => true,
            'data' => [
                'basic' => $basicStats,
                'by_type' => $statsByType
            ]
        ]);
    }

    /**
     * Obtiene modificaciones por usuario creador
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByCreator(Request $request): JsonResponse
    {
        $userId = $request->get('user_id', auth()->id());
        $perPage = $request->get('per_page', 15);
        
        $modifications = $this->modificationService->getModificationsByCreator($userId, $perPage);

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
     * Busca modificaciones por texto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'El término de búsqueda es requerido'
            ], 400);
        }

        $modifications = $this->modificationService->searchModifications($search, $perPage);

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
} 