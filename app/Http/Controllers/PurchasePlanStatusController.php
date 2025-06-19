<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchasePlanStatusResource;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PurchasePlanStatusController extends Controller
{
    /**
     * Obtiene el historial de estados de un plan de compra
     */
    public function getStatusHistory(int $purchasePlanId): JsonResponse
    {
        try {
            $purchasePlan = PurchasePlan::findOrFail($purchasePlanId);
            $statusHistory = PurchasePlanStatus::getStatusHistory($purchasePlanId);

            return response()->json([
                'data' => PurchasePlanStatusResource::collection($statusHistory)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el historial de estados. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el estado actual de un plan de compra
     */
    public function getCurrentStatus(int $purchasePlanId): JsonResponse
    {
        try {
            $purchasePlan = PurchasePlan::findOrFail($purchasePlanId);
            $currentStatus = PurchasePlanStatus::getCurrentStatus($purchasePlanId);

            if (!$currentStatus) {
                return response()->json([
                    'message' => 'No se encontró estado para este plan de compra.'
                ], 404);
            }

            return response()->json([
                'data' => new PurchasePlanStatusResource($currentStatus)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el estado actual. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuevo estado para un plan de compra
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'purchase_plan_id' => 'required|exists:purchase_plans,id',
                'status_purchase_plan_id' => 'required|exists:status_purchase_plans,id',
                'sending_date' => 'nullable|date',
                'sending_comment' => 'nullable|string',
            ]);

            $purchasePlanStatus = new PurchasePlanStatus();
            $purchasePlanStatus->fill($validated);
            $purchasePlanStatus->created_by = auth()->id();
            $purchasePlanStatus->save();

            return response()->json([
                'message' => 'Estado del plan de compra creado exitosamente',
                'data' => new PurchasePlanStatusResource($purchasePlanStatus)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear el estado del plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un estado específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $purchasePlanStatus = PurchasePlanStatus::with(['status', 'createdBy'])->findOrFail($id);

            return response()->json([
                'data' => new PurchasePlanStatusResource($purchasePlanStatus)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Estado no encontrado.'
            ], 404);
        }
    }
} 