<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetAllocationRequest;
use App\Http\Resources\BudgetAllocationResource;
use App\Services\BudgetAllocationService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class BudgetAllocationController extends Controller
{
    use LogsActivity;

    protected $budgetAllocationService;

    public function __construct(BudgetAllocationService $budgetAllocationService)
    {
        $this->budgetAllocationService = $budgetAllocationService;
    }

    public function index(): JsonResponse
    {
        try {
            $results = $this->budgetAllocationService->getAllBudgetAllocations();

            return response()->json([
                'data' => BudgetAllocationResource::collection($results),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las asignaciones presupuestarias.'
            ], 500);
        }
    }

    public function store(BudgetAllocationRequest $request): JsonResponse
    {
        try {
            $budgetAllocation = $this->budgetAllocationService->createBudgetAllocation($request->validated());
            $this->logActivity('create_budget_allocation', 'Usuario creó una asignación presupuestaria con ID: ' . $budgetAllocation->id);

            return response()->json([
                'message' => 'Asignación presupuestaria ha sido guardada exitosamente',
                'data' => new BudgetAllocationResource($budgetAllocation)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar la asignación presupuestaria. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $budgetAllocation = $this->budgetAllocationService->getBudgetAllocationById($id);

            return response()->json([
                'data' => new BudgetAllocationResource($budgetAllocation)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Asignación presupuestaria no encontrada.'
            ], 404);
        }
    }

    public function update(int $id, BudgetAllocationRequest $request): JsonResponse
    {
        try {
            $updated = $this->budgetAllocationService->updateBudgetAllocation($id, $request->validated());
            $this->logActivity('update_budget_allocation', 'Usuario actualizó la asignación presupuestaria con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Asignación presupuestaria ha sido actualizada exitosamente',
                'data' => new BudgetAllocationResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la asignación presupuestaria. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->budgetAllocationService->deleteBudgetAllocation($id);
            $this->logActivity('delete_budget_allocation', 'Usuario eliminó la asignación presupuestaria con ID: ' . $id);

            return response()->json([
                'message' => 'Asignación presupuestaria ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la asignación presupuestaria. ' . $e->getMessage()
            ], 500);
        }
    }
}
