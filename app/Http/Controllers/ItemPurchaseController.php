<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemPurchaseRequest;
use App\Http\Resources\ItemPurchaseResource;
use App\Services\ItemPurchaseService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class ItemPurchaseController extends Controller
{
    use LogsActivity;

    protected $itemPurchaseService;

    public function __construct(ItemPurchaseService $itemPurchaseService)
    {
        $this->itemPurchaseService = $itemPurchaseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $projectToken = $request->query('project_token');
            
            $results = $this->itemPurchaseService->getAllItemPurchasesByQuery($query, $perPage, $projectToken);

            return response()->json([
                'data' => ItemPurchaseResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los ítems de compra.'
            ], 500);
        }
    }

    public function store(ItemPurchaseRequest $request): JsonResponse
    {
        try {
            $itemPurchase = $this->itemPurchaseService->createItemPurchase($request->validated());
            $this->logActivity('create_item_purchase', 'Usuario creó un ítem de compra con ID: ' . $itemPurchase->id);

            return response()->json([
                'message' => 'Ítem de compra ha sido guardado exitosamente',
                'data' => new ItemPurchaseResource($itemPurchase)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el ítem de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $itemPurchase = $this->itemPurchaseService->getItemPurchaseById($id);

            return response()->json([
                'data' => new ItemPurchaseResource($itemPurchase)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ítem de compra no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, ItemPurchaseRequest $request): JsonResponse
    {
        try {
            $updated = $this->itemPurchaseService->updateItemPurchase($id, $request->validated());
            $this->logActivity('update_item_purchase', 'Usuario actualizó el ítem de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Ítem de compra ha sido actualizado exitosamente',
                'data' => new ItemPurchaseResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el ítem de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->itemPurchaseService->deleteItemPurchase($id);
            $this->logActivity('delete_item_purchase', 'Usuario eliminó el ítem de compra con ID: ' . $id);

            return response()->json([
                'message' => 'Ítem de compra ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el ítem de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status_item_purchase_id' => 'required',
            ]); 
            $updated = $this->itemPurchaseService->updateItemPurchaseStatus($id, $validated);
            $this->logActivity('update_item_purchase_status', 'Usuario actualizó el estado del ítem de compra con ID: ' . $updated->id);
            
            return response()->json([
                'message' => 'Estado del ítem de compra actualizado exitosamente',
                'data' => new ItemPurchaseResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado del ítem de compra. ' . $e->getMessage()
            ], 500);
        }
    }
}
