<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitService
{
    /**
     * Verificar límite de importaciones
     */
    public function checkLimit(int $userId): bool
    {
        // En modo desarrollo, permitir todas las importaciones
        if (config('app.debug') || config('app.env') === 'local') {
            return true;
        }

        $key = "import_limit:{$userId}";
        $maxAttempts = config('import.security.max_attempts_per_hour', 10);
        $decayMinutes = config('import.security.decay_minutes', 60);

        // Verificar límite por hora
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        // Incrementar contador
        RateLimiter::hit($key, $decayMinutes * 60);

        return true;
    }

    /**
     * Obtener intentos restantes
     */
    public function getRemainingAttempts(int $userId): int
    {
        $key = "import_limit:{$userId}";
        $maxAttempts = config('import.security.max_attempts_per_hour', 10);
        
        return RateLimiter::remaining($key, $maxAttempts);
    }

    /**
     * Obtener tiempo de espera restante
     */
    public function getRetryAfter(int $userId): int
    {
        $key = "import_limit:{$userId}";
        return RateLimiter::availableIn($key);
    }

    /**
     * Limpiar límites de un usuario
     */
    public function clearLimits(int $userId): void
    {
        $key = "import_limit:{$userId}";
        RateLimiter::clear($key);
    }

    /**
     * Verificar límite de tamaño de archivo por usuario
     */
    public function checkFileSizeLimit(int $userId, int $fileSize): bool
    {
        $key = "file_size_limit:{$userId}";
        $maxTotalSize = config('import.security.max_total_size_per_hour', 100 * 1024 * 1024); // 100MB por hora
        $decayMinutes = 60;

        $currentTotal = Cache::get($key, 0);
        
        if (($currentTotal + $fileSize) > $maxTotalSize) {
            return false;
        }

        // Actualizar total
        Cache::put($key, $currentTotal + $fileSize, $decayMinutes * 60);
        
        return true;
    }

    /**
     * Verificar límite de archivos simultáneos
     */
    public function checkConcurrentLimit(int $userId): bool
    {
        $key = "concurrent_imports:{$userId}";
        $maxConcurrent = config('import.security.max_concurrent_imports', 2);
        $timeout = config('import.security.concurrent_timeout', 300); // 5 minutos

        $current = Cache::get($key, 0);
        
        if ($current >= $maxConcurrent) {
            return false;
        }

        // Incrementar contador
        Cache::put($key, $current + 1, $timeout);
        
        return true;
    }

    /**
     * Liberar slot de importación concurrente
     */
    public function releaseConcurrentSlot(int $userId): void
    {
        $key = "concurrent_imports:{$userId}";
        $current = Cache::get($key, 0);
        
        if ($current > 0) {
            Cache::put($key, $current - 1, config('import.security.concurrent_timeout', 300));
        }
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function isUserBlocked(int $userId): bool
    {
        $blockKey = "user_blocked:{$userId}";
        return Cache::has($blockKey);
    }

    /**
     * Bloquear usuario temporalmente
     */
    public function blockUser(int $userId, int $minutes = 60): void
    {
        $blockKey = "user_blocked:{$userId}";
        Cache::put($blockKey, true, $minutes * 60);
    }

    /**
     * Desbloquear usuario
     */
    public function unblockUser(int $userId): void
    {
        $blockKey = "user_blocked:{$userId}";
        Cache::forget($blockKey);
    }
} 