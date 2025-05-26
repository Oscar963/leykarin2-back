<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypePurchaseRequest;
use App\Http\Resources\TypePurchaseResource;
use App\Services\TypePurchaseService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class TypePurchaseController extends Controller
{
    use LogsActivity;

    protected $typePurchaseService;

    public function __construct(TypePurchaseService $typePurchaseService)
    {
        $this->typePurchaseService = $typePurchaseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->typePurchaseService->getAllTypePurchasesByQuery($query, $perPage);

            return response()->json([
                'data' => TypePurchaseResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de compra.'
            ], 500);
        }
    }

    public function store(TypePurchaseRequest $request): JsonResponse
    {
        try {
            $typePurchase = $this->typePurchaseService->createTypePurchase($request->validated());
            $this->logActivity('create_type_purchase', 'Usuario creó un tipo de compra con ID: ' . $typePurchase->id);

            return response()->json([
                'message' => 'Tipo de compra ha sido guardado exitosamente',
                'data' => new TypePurchaseResource($typePurchase)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el tipo de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $typePurchase = $this->typePurchaseService->getTypePurchaseById($id);

            return response()->json([
                'data' => new TypePurchaseResource($typePurchase)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Tipo de compra no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, TypePurchaseRequest $request): JsonResponse
    {
        try {
            $updated = $this->typePurchaseService->updateTypePurchase($id, $request->validated());
            $this->logActivity('update_type_purchase', 'Usuario actualizó el tipo de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Tipo de compra ha sido actualizado exitosamente',
                'data' => new TypePurchaseResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el tipo de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->typePurchaseService->deleteTypePurchase($id);
            $this->logActivity('delete_type_purchase', 'Usuario eliminó el tipo de compra con ID: ' . $id);

            return response()->json([
                'message' => 'Tipo de compra ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el tipo de compra. ' . $e->getMessage()
            ], 500);
        }
    }
}
