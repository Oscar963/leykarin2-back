<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryPurchaseHistoryResource;
use App\Models\HistoryPurchaseHistory;
use App\Models\PurchasePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\StreamedResponse;

class HistoryPurchaseHistoryController extends Controller
{
    /**
     * Obtiene el historial de movimientos de un plan de compra
     */
    public function getMovementHistory(int $purchasePlanId, Request $request): JsonResponse
    {
        try {
            $purchasePlan = PurchasePlan::findOrFail($purchasePlanId);

            $query = HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                ->with(['status', 'user'])
                ->orderBy('date', 'desc');

            // Filtro por tipo de acción
            if ($request->has('action_type')) {
                $query->where('action_type', $request->action_type);
            }

            // Filtro por fecha
            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            // Filtro por usuario
            if ($request->has('user')) {
                $query->where('user', 'LIKE', "%{$request->user}%");
            }

            $perPage = $request->get('per_page', 50);
            $history = $query->paginate($perPage);

            return response()->json([
                'data' => HistoryPurchaseHistoryResource::collection($history),
                'pagination' => [
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                    'per_page' => $history->perPage(),
                    'total' => $history->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el historial de movimientos. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene un movimiento específico del historial
     */
    public function show(int $id): JsonResponse
    {
        try {
            $history = HistoryPurchaseHistory::with(['status', 'user', 'purchasePlan'])
                ->findOrFail($id);

            return response()->json([
                'data' => new HistoryPurchaseHistoryResource($history)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Movimiento no encontrado.'
            ], 404);
        }
    }

    /**
     * Obtiene estadísticas del historial de un plan de compra
     */
    public function getStatistics(int $purchasePlanId): JsonResponse
    {
        try {
            $purchasePlan = PurchasePlan::findOrFail($purchasePlanId);

            $statistics = [
                'total_movements' => HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)->count(),
                'movements_by_type' => HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                    ->selectRaw('action_type, COUNT(*) as count')
                    ->groupBy('action_type')
                    ->get(),
                'movements_by_user' => HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                    ->selectRaw('user, COUNT(*) as count')
                    ->groupBy('user')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'first_movement' => HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                    ->orderBy('date', 'asc')
                    ->first(),
                'last_movement' => HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                    ->orderBy('date', 'desc')
                    ->first(),
            ];

            return response()->json([
                'data' => $statistics
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporta el historial de movimientos a CSV
     */
    public function export(int $purchasePlanId, Request $request)
    {
        try {
            $purchasePlan = PurchasePlan::findOrFail($purchasePlanId);

            $query = HistoryPurchaseHistory::where('purchase_plan_id', $purchasePlanId)
                ->with(['status', 'user'])
                ->orderBy('date', 'desc');

            // Aplicar filtros si existen
            if ($request->has('action_type')) {
                $query->where('action_type', $request->action_type);
            }

            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            $history = $query->get();

            // Generar CSV
            $filename = "historial_plan_compra_{$purchasePlanId}_" . date('Y-m-d_H-i-s') . ".csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($history) {
                $file = fopen('php://output', 'w');

                // Headers del CSV
                fputcsv($file, [
                    'ID',
                    'Fecha',
                    'Descripción',
                    'Usuario',
                    'Tipo de Acción',
                    'Estado',
                    'Detalles'
                ]);

                // Datos
                foreach ($history as $record) {
                    fputcsv($file, [
                        $record->id,
                        $record->date->format('Y-m-d H:i:s'),
                        $record->description,
                        $record->user,
                        $record->action_type,
                        $record->status ? $record->status->name : 'N/A',
                        json_encode($record->details)
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al exportar el historial. ' . $e->getMessage()
            ], 500);
        }
    }
}
