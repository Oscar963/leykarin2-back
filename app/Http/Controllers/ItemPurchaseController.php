<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemPurchaseRequest;
use App\Http\Resources\ItemPurchaseResource;
use App\Services\ItemPurchaseService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsPurchaseExport;
use App\Exports\ItemsPurchaseTemplateExport;
use App\Imports\ItemsPurchaseImport;
use Illuminate\Support\Facades\Response;

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
            $projectToken = $request->query('projectToken');

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
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No es posible guardar el ítem de compra')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 403);
            }

            return response()->json([
                'message' => 'Error al guardar el ítem de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $itemPurchase = $this->itemPurchaseService->getItemPurchaseById($id);
            $itemPurchase->load('publicationMonth');

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
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No es posible actualizar el ítem de compra')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 403);
            }

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

    public function export($projectId)
    {
        $this->logActivity('download_file', 'Usuario exporto el excel de ítems de compra');
        return Excel::download(new ItemsPurchaseExport($projectId), 'items-purchases.xlsx');
    }



    public function import(Request $request, $projectId)
    {
        // Aumentar tiempo límite para importaciones grandes
        set_time_limit(300); // 5 minutos

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:10240', // Máximo 10MB
            ]);

            $import = new ItemsPurchaseImport($projectId);

            // Importar sin validación automática para mejor rendimiento
            Excel::import($import, $request->file('file'), null, \Maatwebsite\Excel\Excel::XLSX);

            $stats = $import->getImportStats();
            $errors = $import->getErrors();

            // Reordenar números de ítems después de la importación exitosa
            if ($stats['imported'] > 0) {
                $this->itemPurchaseService->reorderItemNumbers($projectId);
            }

            // Determinar mensaje y estado basado en resultados
            $message = $this->generateImportMessage($stats, $errors);
            $success = $stats['imported'] > 0; // Solo exitoso si se importó al menos 1 ítem
            $statusCode = $stats['imported'] > 0 ? 200 : 422; // 422 si no se importó nada

            $this->logActivity('import_file', "Usuario procesó {$stats['total_processed']} ítems: {$stats['imported']} importados, {$stats['skipped']} omitidos para el proyecto {$projectId}");

            return response()->json([
                'message' => $message,
                'stats' => $stats,
                'errors' => $errors,
                'success' => $success
            ], $statusCode);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            return response()->json([
                'message' => 'Errores de validación en el archivo',
                'errors' => $errors,
                'success' => false
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al importar el archivo: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $this->logActivity('download_template', 'Usuario descargó la plantilla de ítems de compra');
        return Excel::download(new ItemsPurchaseTemplateExport(), 'plantilla-items-compra.xlsx');
    }

    /**
     * Genera un mensaje inteligente basado en los resultados de la importación
     */
    private function generateImportMessage($stats, $errors)
    {
        $imported = $stats['imported'];
        $skipped = $stats['skipped'];
        $total = $stats['total_processed'];

        // Caso 1: Todo se importó correctamente
        if ($imported === $total && $skipped === 0) {
            return "Importación completada exitosamente. Se importaron {$imported} ítems.";
        }

        // Caso 2: No se importó nada
        if ($imported === 0) {
            $budgetErrors = 0;
            $validationErrors = 0;
            $otherErrors = 0;

            foreach ($errors as $error) {
                if (str_contains($error['error'], 'VALIDACIÓN PRESUPUESTARIA')) {
                    $budgetErrors++;
                } elseif (str_contains($error['error'], 'obligatorio')) {
                    $validationErrors++;
                } else {
                    $otherErrors++;
                }
            }

            $message = "No se pudo importar ningún ítem. Total procesado: {$total}.";
            
            if ($budgetErrors > 0) {
                $message .= " {$budgetErrors} ítems exceden el presupuesto disponible.";
            }
            if ($validationErrors > 0) {
                $message .= " {$validationErrors} ítems tienen campos obligatorios faltantes.";
            }
            if ($otherErrors > 0) {
                $message .= " {$otherErrors} ítems tienen otros errores.";
            }

            return $message;
        }

        // Caso 3: Importación parcial
        return "Importación parcialmente exitosa. Se importaron {$imported} de {$total} ítems. {$skipped} ítems fueron omitidos por errores.";
    }
}
