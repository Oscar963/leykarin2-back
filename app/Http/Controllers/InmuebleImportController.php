<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ImportServiceInterface;
use App\DTOs\Import\ImportResultDTO;
use App\Exceptions\Import\ImportException;
use App\Exceptions\Import\RateLimitExceededException;
use App\Exceptions\Import\FileValidationException;
use App\Exports\InmueblesTemplateExport;
use App\Imports\InmueblesImport;
use App\Models\ImportHistory;
use App\Models\Inmueble;
use App\Services\InmuebleImportService;
use App\Services\ImportHistoryService;
use App\Services\Validation\FileValidationService;
use App\Services\Security\RateLimitService;
use App\Services\Logging\ImportLogService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class InmuebleImportController extends Controller
{
    use LogsActivity;
    
    protected ImportServiceInterface $importService;
    protected FileValidationService $fileValidationService;
    protected RateLimitService $rateLimitService;
    protected ImportLogService $importLogService;
    protected ImportHistoryService $importHistoryService;

    public function __construct(
        ImportServiceInterface $importService,
        FileValidationService $fileValidationService,
        RateLimitService $rateLimitService,
        ImportLogService $importLogService,
        ImportHistoryService $importHistoryService
    ) {
        $this->importService = $importService;
        $this->fileValidationService = $fileValidationService;
        $this->rateLimitService = $rateLimitService;
        $this->importLogService = $importLogService;
        $this->importHistoryService = $importHistoryService;
    }

    /**
     * Procesar la importación del archivo Excel
     */
    /**
     * Process file import with professional error handling
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $importHistory = null;
        $file = null;
        $userId = Auth::id();
        
        try {
            // Validate request
            $this->validateImportRequest($request);
            
            $file = $request->file('file');

            // Crear registro de historial
            $importHistory = $this->importHistoryService->createImportHistory(
                $file,
                $userId,
                'inmuebles',
                config('import.inmuebles', [])
            );

            // Marcar como iniciada
            $importHistory->markAsStarted();

            // Process import through service with history
            $result = $this->importService->processImportWithHistory($file, $userId, $importHistory); 
            
            // Obtener datos del resultado de forma segura
            $resultData = $result->getData();
            $statistics = $resultData['statistics'] ?? [];
            $errors = $resultData['errors'] ?? [];
            $warnings = $resultData['warnings'] ?? [];
            
            // Actualizar historial con estadísticas
            $this->importHistoryService->updateImportHistory(
                $importHistory,
                $statistics,
                $errors,
                $warnings
            );

            // Marcar como completada
            $importHistory->markAsCompleted($statistics);
            
            // Log activity con verificación segura
            $importedCount = $statistics['imported'] ?? 0;
            $fileName = $resultData['file_name'] ?? 'archivo_desconocido';
            $this->logActivity('import_inmuebles', "Usuario importó {$importedCount} inmuebles desde {$fileName}");
            
            return $result->toResponse();

        } catch (RateLimitExceededException $e) {
            if ($importHistory) {
                $importHistory->markAsFailed('Rate limit excedido');
            }
            $this->importLogService->logRateLimitExceeded($userId);
            return $e->render($request);
            
        } catch (FileValidationException $e) {
            if ($importHistory) {
                $importHistory->markAsFailed('Error de validación de archivo: ' . $e->getMessage());
            }
            $fileName = $file ? $file->getClientOriginalName() : 'archivo_desconocido';
            $this->importLogService->logFileValidation($userId, $fileName, false, [$e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación de archivo',
                    'details' => $e->getMessage()
                ],
                'timestamp' => now()->toISOString(),
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura validaciones estándar de Laravel
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación de datos',
                    'details' => $e->errors()
                ],
                'timestamp' => now()->toISOString(),
            ], 422);
        } catch (ImportException $e) {
            if ($importHistory) {
                $importHistory->markAsFailed('Error de importación: ' . $e->getMessage());
            }
            $fileName = $file ? $file->getClientOriginalName() : null;
            $this->importLogService->logImportError($userId, $e->getMessage(), $fileName);
            return $e->render($request);
            
        } catch (Exception $e) {
            if ($importHistory) {
                $importHistory->markAsFailed('Error inesperado: ' . $e->getMessage());
            }
            $fileName = $file ? $file->getClientOriginalName() : null;
            $this->importLogService->logImportError($userId, $e->getMessage(), $fileName);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNEXPECTED_ERROR',
                    'message' => 'Error inesperado durante la importación',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Validate import request
     *
     * @param Request $request
     * @throws FileValidationException
     */
    private function validateImportRequest(Request $request): void
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:' . implode(',', config('import.allowed_types', ['xlsx', 'xls', 'csv'])),
                'max:' . config('import.max_file_size', 10240)
            ]
        ], [
            'file.required' => 'Debe seleccionar un archivo para importar.',
            'file.mimes' => 'El archivo debe ser de tipo: ' . implode(', ', config('import.allowed_types', ['xlsx', 'xls', 'csv'])),
            'file.max' => 'El archivo no puede ser mayor a ' . (config('import.max_file_size', 10240) / 1024) . 'MB.'
        ]);
    }



    /**
     * Construir mensaje de importación basado en estadísticas
     */
    private function buildImportMessage(array $stats): string
    {
        $messages = [];
        
        if ($stats['imported'] > 0) {
            $messages[] = "✅ {$stats['imported']} inmuebles importados exitosamente";
        }
        
        if ($stats['duplicates'] > 0) {
            $messages[] = "⚠️ {$stats['duplicates']} duplicados omitidos";
        }
        
        if ($stats['skipped'] > 0) {
            $skippedWithoutDuplicates = $stats['skipped'] - $stats['duplicates'];
            if ($skippedWithoutDuplicates > 0) {
                $messages[] = "❌ {$skippedWithoutDuplicates} registros con errores";
            }
        }
        
        if (empty($messages)) {
            return "No se procesaron registros.";
        }
        
        return implode(', ', $messages) . ".";
    }

    /**
     * Vista previa del archivo (opcional)
     * Muestra las primeras filas para confirmar el mapeo
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            // Usar configuraciones del .env
            $maxFileSize = config('import.max_file_size', 10240);
            $allowedTypes = config('import.allowed_types', ['xlsx', 'xls', 'csv']);
            
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:' . implode(',', $allowedTypes),
                    'max:' . $maxFileSize
                ]
            ]);

            $file = $request->file('file');
            
            // Leer solo las primeras filas para preview (configurable)
            $previewRows = config('import.preview.rows', 5);
            $data = Excel::toArray(new InmueblesImport(), $file);
            $preview = array_slice($data[0], 0, $previewRows);

            return response()->json([
                'success' => true,
                'message' => 'Vista previa generada correctamente.',
                'data' => [
                    'headers' => $preview[0] ?? [],
                    'rows' => array_slice($preview, 1), // Sin headers
                    'total_rows' => count($data[0]) - 1, // -1 por el header
                    'file_name' => $file->getClientOriginalName(),
                    'preview_rows' => $previewRows
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar plantilla de Excel (opcional)
     */
    public function downloadTemplate()
    {
        $this->logActivity('download_template', 'Usuario descargó la plantilla de inmuebles');
        
        // Usar configuración para el nombre del archivo
        $fileName = config('import.template.filename', 'plantilla_inmuebles.xlsx');
        
        return Excel::download(new InmueblesTemplateExport(), $fileName);
    }

    /**
     * Contar registros importados (método auxiliar)
     */
    private function countImportedRecords(): int
    {
        // Podrías implementar una lógica más sofisticada aquí
        // Por ejemplo, contar los registros creados en los últimos minutos
        return \App\Models\Inmueble::count();
    }

    /**
     * Obtener mapeo de columnas disponibles (para frontend)
     */
    public function getColumnMapping(): JsonResponse
    {
        $mapping = [
            'numero' => ['Número', 'N°', 'N', 'Item'],
            'descripcion' => ['Descripción', 'Description'],
            'calle' => ['Calle', 'Avenida', 'Pasaje', 'Avenida/Calle/Pasaje'],
            'numeracion' => ['Numeración', 'Número Calle'],
            'lote_sitio' => ['Lote/Sitio', 'Lote', 'Sitio'],
            'manzana' => ['Manzana', 'Mz'],
            'poblacion_villa' => ['Población/Villa', 'Población', 'Villa'],
            'foja' => ['Foja', 'Fs'],
            'inscripcion_numero' => ['Inscripción Número', 'Nro Inscripción'],
            'inscripcion_anio' => ['Inscripción Año', 'Año Inscripción'],
            'rol_avaluo' => ['Rol Avalúo', 'Rol'],
            'superficie' => ['Superficie', 'Sup', 'M²'],
            'deslinde_norte' => ['Deslinde Norte', 'Norte'],
            'deslinde_sur' => ['Deslinde Sur', 'Sur'],
            'deslinde_este' => ['Deslinde Este', 'Este'],
            'deslinde_oeste' => ['Deslinde Oeste', 'Oeste'],
            'decreto_incorporacion' => ['Decreto Incorporación', 'Dcto Incorporación'],
            'decreto_destinacion' => ['Decreto Destinación', 'Dcto Destinación'],
            'observaciones' => ['Observaciones', 'Obs', 'Comentarios', 'Notas'],
        ];

        return response()->json([
            'success' => true,
            'data' => $mapping
        ]);
    }
}
