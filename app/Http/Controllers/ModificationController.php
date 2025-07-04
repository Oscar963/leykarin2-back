<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModificationRequest;
use App\Http\Resources\ModificationResource;
use App\Services\ModificationService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ModificationController extends Controller
{
    use LogsActivity;

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
        try {
            $query = $request->query('q');
            $perPage = $request->query('show', 15);
            $status = $request->query('status');
            $modificationTypeId = $request->query('modification_type_id');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $modifications = $this->modificationService->getAllModificationsByQuery(
                $query, 
                $perPage, 
                $status, 
                $modificationTypeId, 
                $startDate, 
                $endDate
            );

            return response()->json([
                'data' => ModificationResource::collection($modifications)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las modificaciones.'
            ], 500);
        }
    }

    /**
     * Obtiene una modificación específica
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $modification = $this->modificationService->getModificationById($id);

            return response()->json([
                'data' => new ModificationResource($modification)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Modificación no encontrada.'
            ], 404);
        }
    }

    /**
     * Crea una nueva modificación
     *
     * @param ModificationRequest $request
     * @return JsonResponse
     */
    public function store(ModificationRequest $request): JsonResponse
    {
        try {
            $modification = $this->modificationService->createModification($request->validated());
            $this->logActivity('create_modification', 'Usuario creó una modificación con ID: ' . $modification->id);

            return response()->json([
                'message' => 'Modificación ha sido guardada exitosamente',
                'data' => new ModificationResource($modification)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar la modificación. ' . $e->getMessage()
            ], 500);
        }
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
        try {
            $modification = $this->modificationService->updateModification($id, $request->validated());
            $this->logActivity('update_modification', 'Usuario actualizó la modificación con ID: ' . $modification->id);

            return response()->json([
                'message' => 'Modificación ha sido actualizada exitosamente',
                'data' => new ModificationResource($modification)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la modificación. ' . $e->getMessage()
            ], 500);
        }
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
        try {
            $request->validate([
                'status' => 'required|string|in:active,inactive,pending,approved,rejected'
            ]);

            $modification = $this->modificationService->changeModificationStatus($id, $request->status);
            $this->logActivity('change_modification_status', 'Usuario cambió el estado de la modificación con ID: ' . $id . ' a: ' . $request->status);

            return response()->json([
                'message' => 'Estado de la modificación actualizado exitosamente',
                'data' => new ModificationResource($modification)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al cambiar el estado de la modificación. ' . $e->getMessage()
            ], 500);
        }
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
        try {
            $query = $request->query('q');
            $perPage = $request->query('show', 15);
            
            $modifications = $this->modificationService->getModificationsByPurchasePlan($purchasePlanId, $query, $perPage);

            return response()->json([
                'data' => ModificationResource::collection($modifications)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las modificaciones del plan de compra.'
            ], 500);
        }
    }

    /**
     * Obtiene modificaciones pendientes de aprobación
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPendingApproval(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('show', 15);
            $modifications = $this->modificationService->getPendingApprovalModifications($perPage);

            return response()->json([
                'data' => ModificationResource::collection($modifications)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las modificaciones pendientes de aprobación.'
            ], 500);
        }
    }

    /**
     * Elimina una modificación
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->modificationService->deleteModification($id);
            $this->logActivity('delete_modification', 'Usuario eliminó la modificación con ID: ' . $id);

            return response()->json([
                'message' => 'Modificación ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la modificación. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los estados disponibles
     *
     * @return JsonResponse
     */
    public function getAvailableStatuses(): JsonResponse
    {
        try {
            $statuses = $this->modificationService->getAvailableStatuses();

            return response()->json([
                'data' => $statuses
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los estados disponibles.'
            ], 500);
        }
    }

    /**
     * Obtiene los tipos de modificación disponibles
     *
     * @return JsonResponse
     */
    public function getAvailableTypes(): JsonResponse
    {
        try {
            $types = $this->modificationService->getAvailableTypes();

            return response()->json([
                'data' => $types
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de modificación disponibles.'
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas básicas de modificaciones
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $basicStats = $this->modificationService->getBasicStatistics();
            $statsByType = $this->modificationService->getStatisticsByType();

            return response()->json([
                'message' => 'Estadísticas de modificaciones obtenidas exitosamente',
                'data' => [
                    'basic' => $basicStats,
                    'by_type' => $statsByType
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las estadísticas de modificaciones. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene modificaciones por usuario creador
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByCreator(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id', auth()->id());
            $perPage = $request->query('show', 15);
            
            $modifications = $this->modificationService->getModificationsByCreator($userId, $perPage);

            return response()->json([
                'data' => ModificationResource::collection($modifications)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las modificaciones del usuario.'
            ], 500);
        }
    }

    /**
     * Busca modificaciones por texto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->query('q');
            $perPage = $request->query('show', 15);

            if (!$search) {
                return response()->json([
                    'message' => 'El término de búsqueda es requerido'
                ], 400);
            }

            $modifications = $this->modificationService->searchModifications($search, $perPage);

            return response()->json([
                'data' => ModificationResource::collection($modifications)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al buscar modificaciones.'
            ], 500);
        }
    }

    /**
     * Obtiene un dashboard con estadísticas de modificaciones
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $purchasePlanId = $request->query('purchase_plan_id');

            $basicStats = $this->modificationService->getBasicStatistics();
            $statsByType = $this->modificationService->getStatisticsByType();

            $dashboardData = [
                'general_statistics' => $basicStats,
                'statistics_by_type' => $statsByType
            ];

            // Si se especifica un plan de compra, obtener modificaciones específicas
            if ($purchasePlanId) {
                $modifications = $this->modificationService->getModificationsByPurchasePlan($purchasePlanId, null, 1000);
                $dashboardData['modifications'] = ModificationResource::collection($modifications);
            }

            return response()->json([
                'message' => 'Dashboard de modificaciones obtenido exitosamente',
                'data' => $dashboardData
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el dashboard de modificaciones: ' . $e->getMessage()
            ], 500);
        }
    }
} 