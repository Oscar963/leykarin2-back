<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitPurchasingRequest;
use App\Http\Resources\UnitPurchasingResource;
use App\Services\UnitPurchasingService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class UnitPurchasingController extends Controller
{
    use LogsActivity;

    protected $unitPurchasingService;

    public function __construct(UnitPurchasingService $unitPurchasingService)
    {
        $this->unitPurchasingService = $unitPurchasingService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->unitPurchasingService->getAllUnitPurchasingsByQuery($query, $perPage);

            return response()->json([
                'data' => UnitPurchasingResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las unidades de compra.'
            ], 500);
        }
    }

    public function store(UnitPurchasingRequest $request): JsonResponse
    {
        try {
            $unitPurchasing = $this->unitPurchasingService->createUnitPurchasing($request->validated());
            $this->logActivity('create_unit_purchasing', 'Usuario creó una unidad de compra con ID: ' . $unitPurchasing->id);

            return response()->json([
                'message' => 'Unidad de compra ha sido guardada exitosamente',
                'data' => new UnitPurchasingResource($unitPurchasing)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar la unidad de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $unitPurchasing = $this->unitPurchasingService->getUnitPurchasingById($id);

            return response()->json([
                'data' => new UnitPurchasingResource($unitPurchasing)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unidad de compra no encontrada.'
            ], 404);
        }
    }

    public function update(int $id, UnitPurchasingRequest $request): JsonResponse
    {
        try {
            $updated = $this->unitPurchasingService->updateUnitPurchasing($id, $request->validated());
            $this->logActivity('update_unit_purchasing', 'Usuario actualizó la unidad de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Unidad de compra ha sido actualizada exitosamente',
                'data' => new UnitPurchasingResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la unidad de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->unitPurchasingService->deleteUnitPurchasing($id);
            $this->logActivity('delete_unit_purchasing', 'Usuario eliminó la unidad de compra con ID: ' . $id);

            return response()->json([
                'message' => 'Unidad de compra ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la unidad de compra. ' . $e->getMessage()
            ], 500);
        }
    }
}
