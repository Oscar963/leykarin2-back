<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Services\ImportHistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ImportHistoryController extends Controller
{
    protected ImportHistoryService $importHistoryService;

    public function __construct(ImportHistoryService $importHistoryService)
    {
        $this->importHistoryService = $importHistoryService;
    }

    /**
     * Obtener historial de importaciones del usuario
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $perPage = $request->get('per_page', 15);
            
            $filters = $request->only([
                'status', 'type', 'date_from', 'date_to'
            ]);

            $history = $this->importHistoryService->getUserImportHistory(
                $userId,
                $perPage,
                $filters
            );

            return response()->json([
                'success' => true,
                'message' => 'Historial de importaciones obtenido correctamente',
                'data' => [
                    'imports' => $history->items(),
                    'pagination' => [
                        'current_page' => $history->currentPage(),
                        'last_page' => $history->lastPage(),
                        'per_page' => $history->perPage(),
                        'total' => $history->total(),
                        'from' => $history->firstItem(),
                        'to' => $history->lastItem(),
                    ],
                    'filters' => $filters
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una importación específica
     */
    public function show(string $importId): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $importHistory = ImportHistory::where('import_id', $importId)
                ->where('user_id', $userId)
                ->first();

            if (!$importHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Importación no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detalles de importación obtenidos correctamente',
                'data' => [
                    'import' => $importHistory,
                    'statistics' => $importHistory->getStatistics(),
                    'imported_records' => $importHistory->importedRecords()
                        ->with('importHistory')
                        ->paginate(50)
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de importaciones del usuario
     */
    public function statistics(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $stats = $this->importHistoryService->getUserImportStatistics($userId);

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realizar rollback de una importación
     */
    public function rollback(string $importId): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $importHistory = ImportHistory::where('import_id', $importId)
                ->where('user_id', $userId)
                ->first();

            if (!$importHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Importación no encontrada'
                ], 404);
            }

            if (!$importHistory->canPerformRollback()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede realizar rollback de esta importación'
                ], 400);
            }

            $result = $this->importHistoryService->performRollback($importHistory, $userId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'rolled_back_count' => $result['rolled_back_count'],
                        'errors' => $result['errors'] ?? []
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar rollback: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de versiones de una importación
     */
    public function versionHistory(string $importId): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Verificar que el usuario tenga acceso a esta importación
            $importHistory = ImportHistory::where('import_id', $importId)
                ->where('user_id', $userId)
                ->first();

            if (!$importHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Importación no encontrada'
                ], 404);
            }

            $versionHistory = $this->importHistoryService->getImportVersionHistory($importId);

            return response()->json([
                'success' => true,
                'message' => 'Historial de versiones obtenido correctamente',
                'data' => [
                    'versions' => $versionHistory,
                    'total_versions' => $versionHistory->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de versiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva versión de una importación
     */
    public function createVersion(string $importId): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $importHistory = ImportHistory::where('import_id', $importId)
                ->where('user_id', $userId)
                ->first();

            if (!$importHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Importación no encontrada'
                ], 404);
            }

            $newVersion = $importHistory->createNewVersion();

            return response()->json([
                'success' => true,
                'message' => 'Nueva versión creada correctamente',
                'data' => [
                    'new_version' => $newVersion,
                    'version_number' => $newVersion->version
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nueva versión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de importaciones recientes (para dashboard)
     */
    public function recentSummary(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            
            $summary = $this->importHistoryService->getRecentImportsSummary($days);

            return response()->json([
                'success' => true,
                'message' => 'Resumen obtenido correctamente',
                'data' => $summary
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar historial de importaciones
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $request->validate([
                'format' => 'in:csv,xlsx,json',
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'status' => 'in:pending,processing,completed,failed,cancelled'
            ]);

            $filters = $request->only(['status', 'date_from', 'date_to']);
            $format = $request->get('format', 'xlsx');

            // Aquí implementarías la lógica de exportación
            // Por ahora retornamos un mensaje de éxito

            return response()->json([
                'success' => true,
                'message' => 'Exportación iniciada correctamente',
                'data' => [
                    'format' => $format,
                    'filters' => $filters,
                    'download_url' => null // URL del archivo generado
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de exportación inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar importación del historial (solo para administradores)
     */
    public function destroy(string $importId): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Verificar permisos de administrador
            if (!Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar importaciones'
                ], 403);
            }

            $importHistory = ImportHistory::where('import_id', $importId)->first();

            if (!$importHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Importación no encontrada'
                ], 404);
            }

            // Eliminar registros importados primero
            $importHistory->importedRecords()->delete();
            
            // Eliminar el historial
            $importHistory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Importación eliminada correctamente'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
} 