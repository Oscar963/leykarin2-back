<?php

namespace App\Services;

use App\Models\ImportHistory;
use App\Models\ImportedRecord;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ImportHistoryService
{
    /**
     * Crear nuevo registro de historial de importación
     */
    public function createImportHistory(
        UploadedFile $file,
        int $userId,
        string $type = 'inmuebles',
        array $config = []
    ): ImportHistory {
        $user = User::find($userId);
        
        return ImportHistory::create([
            'type' => $type,
            'status' => ImportHistory::STATUS_PENDING,
            'file_name' => $this->sanitizeFileName($file->getClientOriginalName()),
            'file_original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'user_id' => $userId,
            'user_name' => $user ? $user->name : 'Usuario Desconocido',
            'user_email' => $user ? $user->email : null,
            'import_config' => $config,
            'column_mapping' => $this->getColumnMapping(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Actualizar historial con estadísticas de importación
     */
    public function updateImportHistory(
        ImportHistory $importHistory,
        array $stats,
        array $errors = [],
        array $warnings = []
    ): void {
        $importHistory->update([
            'total_rows' => $stats['total_processed'] ?? 0,
            'imported_count' => $stats['imported'] ?? 0,
            'skipped_count' => $stats['skipped'] ?? 0,
            'duplicates_count' => $stats['duplicates'] ?? 0,
            'error_count' => count($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'error_summary' => $this->generateErrorSummary($errors),
            'can_rollback' => ($stats['imported'] ?? 0) > 0,
        ]);
    }

    /**
     * Registrar un registro importado para rollback
     */
    public function registerImportedRecord(
        ImportHistory $importHistory,
        string $tableName,
        int $recordId,
        array $originalData,
        array $processedData = null,
        int $rowNumber
    ): ImportedRecord {
        $rowHash = ImportedRecord::generateRowHash($originalData);
        
        return ImportedRecord::createFromImport(
            $importHistory->id,
            $tableName,
            $recordId,
            $originalData,
            $processedData,
            $rowNumber,
            $rowHash
        );
    }

    /**
     * Obtener historial de importaciones por usuario
     */
    public function getUserImportHistory(
        int $userId,
        int $perPage = 15,
        array $filters = []
    ) {
        $query = ImportHistory::byUser($userId);

        // Aplicar filtros
        if (isset($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Obtener estadísticas de importaciones por usuario
     */
    public function getUserImportStatistics(int $userId): array
    {
        $imports = ImportHistory::byUser($userId);

        return [
            'total_imports' => $imports->count(),
            'successful_imports' => $imports->successful()->count(),
            'failed_imports' => $imports->failed()->count(),
            'total_records_imported' => $imports->sum('imported_count'),
            'total_records_skipped' => $imports->sum('skipped_count'),
            'total_duplicates' => $imports->sum('duplicates_count'),
            'total_errors' => $imports->sum('error_count'),
            'average_success_rate' => $this->calculateAverageSuccessRate($imports),
            'total_processing_time' => $imports->sum('processing_time_ms'),
            'recent_imports' => $imports->recent(7)->count(),
        ];
    }

    /**
     * Obtener historial de versiones de una importación
     */
    public function getImportVersionHistory(string $importId): \Illuminate\Database\Eloquent\Collection
    {
        return ImportHistory::where('import_id', $importId)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Realizar rollback de una importación
     */
    public function performRollback(ImportHistory $importHistory, int $userId): array
    {
        if (!$importHistory->canPerformRollback()) {
            return [
                'success' => false,
                'message' => 'No se puede realizar rollback de esta importación'
            ];
        }

        DB::beginTransaction();
        try {
            $importedRecords = $importHistory->importedRecords()
                ->where('status', ImportedRecord::STATUS_IMPORTED)
                ->get();

            $rolledBackCount = 0;
            $errors = [];

            foreach ($importedRecords as $record) {
                try {
                    $model = $record->getRelatedModel();
                    
                    if ($model) {
                        $model->delete();
                        $record->markAsRolledBack();
                        $rolledBackCount++;
                    } else {
                        $record->markAsError('Modelo no encontrado');
                        $errors[] = "Registro ID {$record->record_id} no encontrado";
                    }
                } catch (\Exception $e) {
                    $record->markAsError($e->getMessage());
                    $errors[] = "Error al revertir registro ID {$record->record_id}: " . $e->getMessage();
                }
            }

            // Actualizar historial
            $importHistory->update([
                'rolled_back_at' => now(),
                'rolled_back_by' => $userId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Rollback completado. {$rolledBackCount} registros revertidos.",
                'rolled_back_count' => $rolledBackCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error durante el rollback: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener resumen de importaciones recientes
     */
    public function getRecentImportsSummary(int $days = 30): array
    {
        $imports = ImportHistory::recent($days);

        return [
            'total_imports' => $imports->count(),
            'successful_imports' => $imports->successful()->count(),
            'failed_imports' => $imports->failed()->count(),
            'total_records_imported' => $imports->sum('imported_count'),
            'average_processing_time' => $imports->avg('processing_time_ms'),
            'top_users' => $this->getTopImportUsers($days),
            'imports_by_type' => $this->getImportsByType($days),
            'daily_stats' => $this->getDailyImportStats($days),
        ];
    }

    /**
     * Obtener usuarios con más importaciones
     */
    private function getTopImportUsers(int $days = 30): array
    {
        return ImportHistory::recent($days)
            ->select('user_id', 'user_name', DB::raw('COUNT(*) as import_count'))
            ->groupBy('user_id', 'user_name')
            ->orderBy('import_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Obtener importaciones por tipo
     */
    private function getImportsByType(int $days = 30): array
    {
        return ImportHistory::recent($days)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Obtener estadísticas diarias
     */
    private function getDailyImportStats(int $days = 30): array
    {
        return ImportHistory::recent($days)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_imports'),
                DB::raw('SUM(imported_count) as total_records'),
                DB::raw('AVG(processing_time_ms) as avg_time')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Calcular tasa de éxito promedio
     */
    private function calculateAverageSuccessRate($imports): float
    {
        $totalImports = $imports->count();
        if ($totalImports === 0) {
            return 0.0;
        }

        $successfulImports = $imports->successful()->count();
        return round(($successfulImports / $totalImports) * 100, 2);
    }

    /**
     * Generar resumen de errores
     */
    private function generateErrorSummary(array $errors): string
    {
        if (empty($errors)) {
            return 'Sin errores';
        }

        $errorTypes = [];
        foreach ($errors as $error) {
            $type = $error['type'] ?? 'general';
            $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
        }

        $summary = [];
        foreach ($errorTypes as $type => $count) {
            $summary[] = "{$count} errores de tipo {$type}";
        }

        return implode(', ', $summary);
    }

    /**
     * Obtener mapeo de columnas actual
     */
    private function getColumnMapping(): array
    {
        return [
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
    }

    /**
     * Sanitizar nombre de archivo
     */
    private function sanitizeFileName(string $fileName): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $sanitized = str_replace(['..', './', '/'], '', $sanitized);
        return substr($sanitized, 0, 255);
    }
} 