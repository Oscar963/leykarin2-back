<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ImportLogService
{
    /**
     * Log de inicio de importación
     */
    public function logImportStart(int $userId, string $fileName, int $fileSize): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::info('Importación iniciada', [
            'user_id' => $userId,
            'file_name' => $this->sanitizeFileName($fileName),
            'file_size_kb' => round($fileSize / 1024, 2),
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => $this->sanitizeUserAgent(request()->userAgent())
        ]);
    }

    /**
     * Log de finalización de importación
     */
    public function logImportComplete(int $userId, array $stats): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::info('Importación completada', [
            'user_id' => $userId,
            'imported_count' => $stats['imported'] ?? 0,
            'skipped_count' => $stats['skipped'] ?? 0,
            'duplicates_count' => $stats['duplicates'] ?? 0,
            'error_count' => $stats['errors'] ?? 0,
            'timestamp' => now()->toISOString(),
            'duration_ms' => $this->getImportDuration()
        ]);
    }

    /**
     * Log de error de importación
     */
    public function logImportError(int $userId, string $error, string $fileName = null): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::error('Error en importación', [
            'user_id' => $userId,
            'error_message' => $this->sanitizeErrorMessage($error),
            'file_name' => $fileName ? $this->sanitizeFileName($fileName) : null,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * Log de validación de archivo
     */
    public function logFileValidation(int $userId, string $fileName, bool $isValid, array $errors = []): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::info('Validación de archivo', [
            'user_id' => $userId,
            'file_name' => $this->sanitizeFileName($fileName),
            'is_valid' => $isValid,
            'error_count' => count($errors),
            'timestamp' => now()->toISOString()
        ]);

        // Log detallado de errores solo en desarrollo
        if (config('app.debug') && !empty($errors)) {
            Log::debug('Errores de validación', [
                'user_id' => $userId,
                'errors' => array_slice($errors, 0, 5) // Solo primeros 5 errores
            ]);
        }
    }

    /**
     * Log de rate limiting
     */
    public function logRateLimitExceeded(int $userId): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::warning('Rate limit excedido', [
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => $this->sanitizeUserAgent(request()->userAgent())
        ]);
    }

    /**
     * Log de bloqueo de usuario
     */
    public function logUserBlocked(int $userId, string $reason): void
    {
        if (!config('import.logging.enabled', true)) {
            return;
        }

        Log::warning('Usuario bloqueado', [
            'user_id' => $userId,
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * Sanitizar nombre de archivo para logging
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remover información sensible del nombre
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $sanitized = str_replace(['..', './', '/'], '', $sanitized);
        
        // Limitar longitud
        return substr($sanitized, 0, 100);
    }

    /**
     * Sanitizar mensaje de error
     */
    private function sanitizeErrorMessage(string $error): string
    {
        // Remover información sensible de errores
        $sanitized = $error;
        
        // Remover rutas de archivo
        $sanitized = preg_replace('/\/[\/\w\-\.]+\.(php|log|txt)/', '[FILE]', $sanitized);
        
        // Remover IPs
        $sanitized = preg_replace('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', '[IP]', $sanitized);
        
        // Remover emails
        $sanitized = preg_replace('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', '[EMAIL]', $sanitized);
        
        return $sanitized;
    }

    /**
     * Sanitizar User Agent
     */
    private function sanitizeUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        // Limitar longitud y remover información sensible
        return substr($userAgent, 0, 200);
    }

    /**
     * Obtener duración de la importación
     */
    private function getImportDuration(): int
    {
        $startTime = session('import_start_time');
        if (!$startTime) {
            return 0;
        }

        return (int) ((microtime(true) - $startTime) * 1000);
    }

    /**
     * Establecer tiempo de inicio
     */
    public function setImportStartTime(): void
    {
        session(['import_start_time' => microtime(true)]);
    }

    /**
     * Limpiar tiempo de inicio
     */
    public function clearImportStartTime(): void
    {
        session()->forget('import_start_time');
    }
} 