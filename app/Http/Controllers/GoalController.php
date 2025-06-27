<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Project;
use App\Http\Resources\GoalResource;
use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
    }

    /**
     * Obtiene todas las metas de un proyecto estratégico
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $projectId = $request->query('project_id');
            $query = $request->query('query');
            $status = $request->query('status');
            $perPage = $request->query('per_page', 10);

            $goals = $this->goalService->getAllGoals($projectId, $query, $status, $perPage);

            return response()->json([
                'message' => 'Metas obtenidas exitosamente',
                'data' => GoalResource::collection($goals->items()),
                'pagination' => [
                    'current_page' => $goals->currentPage(),
                    'per_page' => $goals->perPage(),
                    'total' => $goals->total(),
                    'last_page' => $goals->lastPage(),
                    'from' => $goals->firstItem(),
                    'to' => $goals->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las metas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea una nueva meta para un proyecto estratégico
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'nullable|numeric|min:0',
            'unit_measure' => 'nullable|string|max:100',
            'target_date' => 'nullable|date|after:today',
            'project_id' => 'required|exists:projects,id',
            'notes' => 'nullable|string'
        ]);

        try {
            $goal = $this->goalService->createGoal($request->all());

            return response()->json([
                'message' => 'Meta creada exitosamente',
                'data' => new GoalResource($goal)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la meta',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtiene una meta específica
     */
    public function show($id): JsonResponse
    {
        try {
            $goal = $this->goalService->getGoalById($id);

            return response()->json([
                'message' => 'Meta obtenida exitosamente',
                'data' => new GoalResource($goal)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Meta no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualiza una meta
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'nullable|numeric|min:0',
            'unit_measure' => 'nullable|string|max:100',
            'target_date' => 'nullable|date',
            'status' => 'in:pendiente,en_progreso,completada,cancelada',
            'notes' => 'nullable|string'
        ]);

        try {
            $goal = $this->goalService->updateGoal($id, $request->all());

            return response()->json([
                'message' => 'Meta actualizada exitosamente',
                'data' => new GoalResource($goal)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la meta',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Elimina una meta
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->goalService->deleteGoal($id);

            return response()->json([
                'message' => 'Meta eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la meta',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Actualiza el progreso de una meta
     */
    public function updateProgress(Request $request, $id): JsonResponse
    {
        $request->validate([
            'current_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $goal = $this->goalService->updateGoalProgress(
                $id, 
                $request->input('current_value'),
                $request->input('notes')
            );

            return response()->json([
                'message' => 'Progreso de meta actualizado exitosamente',
                'data' => new GoalResource($goal)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el progreso',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtiene estadísticas de metas por proyecto
     */
    public function getProjectStatistics($projectId): JsonResponse
    {
        try {
            $statistics = $this->goalService->getProjectGoalStatistics($projectId);

            return response()->json([
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene metas vencidas
     */
    public function getOverdueGoals(Request $request): JsonResponse
    {
        try {
            $projectId = $request->query('project_id');
            $perPage = $request->query('per_page', 10);

            $goals = $this->goalService->getOverdueGoals($projectId, $perPage);

            return response()->json([
                'message' => 'Metas vencidas obtenidas exitosamente',
                'data' => GoalResource::collection($goals->items()),
                'pagination' => [
                    'current_page' => $goals->currentPage(),
                    'per_page' => $goals->perPage(),
                    'total' => $goals->total(),
                    'last_page' => $goals->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener metas vencidas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 