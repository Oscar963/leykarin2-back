<?php

namespace App\Services;

use App\Models\ImportHistories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ImportHistoriesService
{
    /**
     * Obtiene todos los inmuebles ordenados por fecha de creación (descendente).
     */
    public function getAllImportHistories()
    {
        return ImportHistories::latest()->get();
    }

    /**
     * Obtiene todos los inmuebles con filtros y paginación.
     */
    public function getAllImportHistoriesByQuery(?string $query, ?int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        return ImportHistories::oldest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('filename', 'LIKE', "%{$query}%")
                    ->orWhere('imported_by', 'LIKE', "%{$query}%")
                    ->orWhere('status', 'LIKE', "%{$query}%")
                    ->orWhere('total_rows', 'LIKE', "%{$query}%")
                    ->orWhere('success_count', 'LIKE', "%{$query}%")
                    ->orWhere('error_count', 'LIKE', "%{$query}%")
                    ->orWhere('error_log', 'LIKE', "%{$query}%")
                    ->orWhere('started_at', 'LIKE', "%{$query}%")
                    ->orWhere('finished_at', 'LIKE', "%{$query}%");
            })
            ->when(!empty($filters), function (Builder $q) use ($filters) {
                $q->where($filters);
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo registro de historial de importación.
     */
    public function createImportHistory(array $data): ImportHistories
    {
        return ImportHistories::create($data);
    }

    /**
     * Inicia un nuevo registro de importación.
     */
    public function startImport(string $filename, int $userId, string $model): ImportHistories
    {
        return ImportHistories::create([
            'filename' => $filename,
            'imported_by' => $userId,
            'model' => $model,
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Finaliza un registro de importación con éxito.
     */
    public function completeImport(ImportHistories $importHistory, int $totalRows, int $successCount, int $errorCount, array $errorLog = []): ImportHistories
    {
        $importHistory->update([
            'status' => 'completed',
            'finished_at' => now(),
            'total_rows' => $totalRows,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'error_log' => $errorLog,
        ]);

        return $importHistory;
    }

    /**
     * Marca una importación como fallida.
     */
    public function failImport(ImportHistories $importHistory, string $errorMessage): ImportHistories
    {
        $importHistory->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_log' => [['error' => $errorMessage, 'timestamp' => now()]],
        ]);

        return $importHistory;
    }

    /**
     * Obtiene un inmueble por su ID.
     */
    public function getImportHistoryById(int $id): ImportHistories
    {
        return ImportHistories::findOrFail($id);
    }

    /**
     * Actualiza un inmueble usando asignación masiva.
     */
    public function updateImportHistory(ImportHistories $importHistory, array $data): ImportHistories
    {
        $importHistory->update($data);
        return $importHistory;
    }

    /**
     * Elimina un inmueble.
     */
    public function deleteImportHistory(ImportHistories $importHistory): ImportHistories
    {
        $importHistory->delete();
        return $importHistory;
    }
}
