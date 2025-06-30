<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;

class ObservabilityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo habilitar en entornos de desarrollo y staging
        if (app()->environment(['local', 'staging'])) {
            $this->enableQueryLogging();
            $this->enableSlowQueryDetection();
            $this->enableCacheMetrics();
        }

        // Habilitar métricas de performance en todos los entornos
        $this->enablePerformanceMetrics();
    }

    /**
     * Habilita logging de queries para debugging
     */
    private function enableQueryLogging(): void
    {
        DB::listen(function (QueryExecuted $query) {
            $executionTime = $query->time;

            // Log solo queries que tomen más de 100ms
            if ($executionTime > 100) {
                Log::channel('slow-queries')->warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $executionTime . 'ms',
                    'connection' => $query->connectionName,
                ]);
            }
        });
    }

    /**
     * Detecta queries extremadamente lentas
     */
    private function enableSlowQueryDetection(): void
    {
        DB::listen(function (QueryExecuted $query) {
            $executionTime = $query->time;

            // Alertar sobre queries que tomen más de 1 segundo
            if ($executionTime > 1000) {
                Log::channel('critical-performance')->critical('Critical Slow Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $executionTime . 'ms',
                    'connection' => $query->connectionName,
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                ]);
            }
        });
    }

    /**
     * Métricas de cache para monitoring
     */
    private function enableCacheMetrics(): void
    {
        // Simplificamos el cache monitoring para evitar conflictos
        // En producción se puede implementar con decorators más sofisticados

        Cache::macro('getWithMetrics', function ($key, $default = null) {
            $startTime = microtime(true);
            $value = Cache::get($key, $default);
            $endTime = microtime(true);

            $cacheHit = $value !== $default;

            Log::debug('Cache Access', [
                'key' => $key,
                'hit' => $cacheHit,
                'time' => round(($endTime - $startTime) * 1000, 2) . 'ms'
            ]);

            return $value;
        });
    }

    /**
     * Métricas generales de performance
     */
    private function enablePerformanceMetrics(): void
    {
        // Registrar middleware para medir tiempos de respuesta
        $this->app['router']->pushMiddlewareToGroup('api', function (Request $request, \Closure $next) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $next($request);

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $executionTime = round(($endTime - $startTime) * 1000, 2);
            $memoryUsage = $endMemory - $startMemory;

            // Log solo requests lentos (>500ms) o que usen mucha memoria (>10MB)
            if ($executionTime > 500 || $memoryUsage > 10485760) {
                Log::channel('performance')->info('Slow Request Detected', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'execution_time' => $executionTime . 'ms',
                    'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                    'status_code' => $response->getStatusCode(),
                    'user_id' => auth()->id(),
                ]);
            }

            // Agregar headers de performance para debugging
            if (app()->environment(['local', 'staging'])) {
                $response->headers->set('X-Execution-Time', $executionTime . 'ms');
                $response->headers->set('X-Memory-Usage', round($memoryUsage / 1024 / 1024, 2) . 'MB');
                $response->headers->set('X-Query-Count', DB::getQueryLog() ? count(DB::getQueryLog()) : 0);
            }

            return $response;
        });
    }
}
