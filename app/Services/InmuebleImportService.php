<?php

namespace App\Services;

use App\Contracts\Services\ImportServiceInterface;
use App\DTOs\Import\ImportResultDTO;
use App\Exceptions\Import\RateLimitExceededException;
use App\Exceptions\Import\FileValidationException;
use App\Imports\InmueblesImport;
use App\Models\ImportHistory;
use App\Services\Validation\FileValidationService;
use App\Services\Security\RateLimitService;
use App\Services\Logging\ImportLogService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InmuebleImportService implements ImportServiceInterface
{
    protected $fileValidationService;
    protected $rateLimitService;
    protected $importLogService;

    public function __construct(
        FileValidationService $fileValidationService,
        RateLimitService $rateLimitService,
        ImportLogService $importLogService
    ) {
        $this->fileValidationService = $fileValidationService;
        $this->rateLimitService = $rateLimitService;
        $this->importLogService = $importLogService;
    }

    /**
     * Process file import with full validation and security checks
     *
     * @param UploadedFile $file
     * @param int $userId
     * @return ImportResultDTO
     * @throws RateLimitExceededException
     * @throws FileValidationException
     */
    public function processImport(UploadedFile $file, int $userId): ImportResultDTO
    {
        // Generate unique import ID
        $importId = Str::uuid()->toString();

        // Verificar rate limiting
        if (!$this->rateLimitService->checkLimit($userId)) {
            $retryAfter = $this->rateLimitService->getRetryAfter($userId);
            throw new RateLimitExceededException($userId, $retryAfter);
        }

        // Validar archivo
        $this->fileValidationService->validateFile($file);

        // Sanitizar nombre de archivo
        $sanitizedFileName = $this->sanitizeFileName($file->getClientOriginalName());

        // Configurar límites
        $this->configureLimits();

        // Log de inicio
        $this->importLogService->logImportStart($userId, $sanitizedFileName, $file->getSize());

        // Procesar importación
        $import = new InmueblesImport();
        Excel::import($import, $file);

        // Obtener estadísticas
        $stats = $import->getImportStats();
        $errors = $import->getErrors();

        // Log de finalización
        $this->importLogService->logImportComplete($userId, $stats);

        return $this->buildResponse($sanitizedFileName, $stats, $errors, $importId);
    }

    /**
     * Process file import with history tracking
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param ImportHistory $importHistory
     * @return ImportResultDTO
     * @throws RateLimitExceededException
     * @throws FileValidationException
     */
    public function processImportWithHistory(UploadedFile $file, int $userId, ImportHistory $importHistory): ImportResultDTO
    {
        // Verificar rate limiting
        if (!$this->rateLimitService->checkLimit($userId)) {
            $retryAfter = $this->rateLimitService->getRetryAfter($userId);
            throw new RateLimitExceededException($userId, $retryAfter);
        }

        // Validar archivo
        $this->fileValidationService->validateFile($file);

        // Sanitizar nombre de archivo
        $sanitizedFileName = $this->sanitizeFileName($file->getClientOriginalName());

        // Configurar límites
        $this->configureLimits();

        // Log de inicio
        $this->importLogService->logImportStart($userId, $sanitizedFileName, $file->getSize());

        // Procesar importación con historial
        $import = new InmueblesImport($importHistory);
        Excel::import($import, $file);

        // Obtener estadísticas
        $stats = $import->getImportStats();
        $errors = $import->getErrors();

        // Log de finalización
        $this->importLogService->logImportComplete($userId, $stats);

        return $this->buildResponse($sanitizedFileName, $stats, $errors, $importHistory->import_id);
    }

    /**
     * Generar vista previa del archivo
     */
    public function generatePreview(UploadedFile $file): array
    {
        $this->fileValidationService->validateFile($file);
        
        $previewRows = config('import.preview.rows', 5);
        $data = Excel::toArray(new InmueblesImport(), $file);
        $preview = array_slice($data[0], 0, $previewRows);

        return [
            'headers' => $preview[0] ?? [],
            'rows' => array_slice($preview, 1),
            'total_rows' => count($data[0]) - 1,
            'file_name' => $this->sanitizeFileName($file->getClientOriginalName()),
            'preview_rows' => $previewRows
        ];
    }

    /**
     * Sanitizar nombre de archivo para prevenir path traversal
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remover caracteres peligrosos y path traversal
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $sanitized = str_replace(['..', './', '/'], '', $sanitized);
        
        // Limitar longitud
        return substr($sanitized, 0, 255);
    }

    /**
     * Configurar límites de memoria y tiempo
     */
    private function configureLimits(): void
    {
        $memoryLimit = config('import.defaults.memory_limit', '512M');
        $timeout = config('import.defaults.timeout', 300);
        
        ini_set('memory_limit', $memoryLimit);
        set_time_limit($timeout);
    }

    /**
     * Build import response as DTO
     */
    private function buildResponse(string $fileName, array $stats, array $errors, ?string $importId = null): ImportResultDTO
    {
        $maxErrors = config('import.validation.max_errors', 10);
        $message = $this->buildImportMessage($stats);
        
        $data = [
            'file_name' => $fileName,
            'statistics' => $stats,
            'has_errors' => !empty($errors),
            'error_count' => count($errors)
        ];

        if (!empty($errors)) {
            $data['errors'] = array_slice($errors, 0, $maxErrors);
            $data['total_errors'] = count($errors);
            
            if (count($errors) > $maxErrors) {
                $data['errors_note'] = "Mostrando solo los primeros {$maxErrors} errores de " . count($errors) . " total.";
            }
        }

        // Determine HTTP status and create appropriate DTO
        if ($stats['imported'] === 0 && $stats['skipped'] > 0) {
            return ImportResultDTO::failure(
                'No se pudo importar ningún registro. Revise los errores.',
                $errors,
                422,
                $importId
            );
        } elseif ($stats['skipped'] > 0) {
            return ImportResultDTO::partial(
                $message . ' Algunos registros fueron omitidos.',
                $data,
                $errors,
                $importId
            );
        }

        return ImportResultDTO::success($message, $data, 200, $importId);
    }

    /**
     * Construir mensaje de importación
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
        
        return empty($messages) ? "No se procesaron registros." : implode(', ', $messages) . ".";
    }

    /**
     * Validate file before processing
     *
     * @param UploadedFile $file
     * @return bool
     * @throws FileValidationException
     */
    public function validateFile(UploadedFile $file): bool
    {
        try {
            $this->fileValidationService->validateFile($file);
            return true;
        } catch (\Exception $e) {
            throw new FileValidationException($e->getMessage());
        }
    }

    /**
     * Get import statistics for monitoring
     *
     * @param int $userId
     * @return array
     */
    public function getImportStatistics(int $userId): array
    {
        return [
            'total_inmuebles' => \App\Models\Inmueble::count(),
            'user_imports_today' => $this->getUserImportsToday($userId),
            'user_imports_this_month' => $this->getUserImportsThisMonth($userId),
            'rate_limit_remaining' => $this->rateLimitService->getRemainingAttempts($userId),
            'rate_limit_reset' => $this->rateLimitService->getRetryAfter($userId),
        ];
    }

    /**
     * Cancel ongoing import process
     *
     * @param int $userId
     * @param string $importId
     * @return bool
     */
    public function cancelImport(int $userId, string $importId): bool
    {
        // Implementation for canceling import
        // This would typically involve checking a cache or database for ongoing imports
        $cacheKey = "import_{$userId}_{$importId}";
        
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            $this->importLogService->logImportError($userId, "Import canceled by user", null);
            return true;
        }
        
        return false;
    }

    /**
     * Get user imports for today
     */
    private function getUserImportsToday(int $userId): int
    {
        // Implementation would query database for today's imports
        return 0; // Placeholder
    }

    /**
     * Get user imports for this month
     */
    private function getUserImportsThisMonth(int $userId): int
    {
        // Implementation would query database for this month's imports
        return 0; // Placeholder
    }
} 