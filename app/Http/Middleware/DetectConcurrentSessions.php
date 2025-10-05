<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para detectar y gestionar sesiones concurrentes.
 * 
 * Este middleware permite:
 * 1. Detectar cuando un usuario tiene múltiples sesiones activas
 * 2. Limitar el número de sesiones concurrentes
 * 3. Notificar al usuario sobre sesiones activas
 * 4. Opcionalmente cerrar sesiones antiguas
 */
class DetectConcurrentSessions
{
    /**
     * Número máximo de sesiones concurrentes permitidas.
     * 0 = ilimitado
     * 
     * @var int
     */
    protected $maxSessions;

    /**
     * Estrategia cuando se excede el límite:
     * - 'notify': Solo notificar, permitir acceso
     * - 'block': Bloquear nueva sesión
     * - 'logout_oldest': Cerrar sesión más antigua
     * 
     * @var string
     */
    protected $strategy;

    public function __construct()
    {
        $this->maxSessions = config('session.max_concurrent_sessions', 3);
        $this->strategy = config('session.concurrent_strategy', 'notify');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            $cacheKey = "user_sessions:{$user->id}";

            // Obtener sesiones activas del usuario
            $activeSessions = Cache::get($cacheKey, []);

            // Agregar información de la sesión actual
            $sessionInfo = [
                'session_id' => $currentSessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity' => now()->toDateTimeString(),
                'started_at' => $activeSessions[$currentSessionId]['started_at'] ?? now()->toDateTimeString()
            ];

            // Actualizar sesión actual
            $activeSessions[$currentSessionId] = $sessionInfo;

            // Limpiar sesiones expiradas (más de 2 horas sin actividad)
            $activeSessions = $this->cleanExpiredSessions($activeSessions);

            // Contar sesiones activas
            $sessionCount = count($activeSessions);

            // Verificar límite de sesiones
            if ($this->maxSessions > 0 && $sessionCount > $this->maxSessions) {
                return $this->handleExcessSessions($user, $activeSessions, $currentSessionId, $request);
            }

            // Guardar sesiones actualizadas en caché (TTL: 2 horas)
            Cache::put($cacheKey, $activeSessions, now()->addHours(2));

            // Agregar información de sesiones al request para uso posterior
            $request->attributes->set('concurrent_sessions', [
                'count' => $sessionCount,
                'max' => $this->maxSessions,
                'sessions' => $activeSessions
            ]);
        }

        return $next($request);
    }

    /**
     * Limpia sesiones expiradas del array.
     *
     * @param array $sessions
     * @return array
     */
    protected function cleanExpiredSessions(array $sessions): array
    {
        $timeout = 7200; // 2 horas en segundos
        $now = now();

        return array_filter($sessions, function ($session) use ($now, $timeout) {
            $lastActivity = \Carbon\Carbon::parse($session['last_activity']);
            return $now->diffInSeconds($lastActivity) < $timeout;
        });
    }

    /**
     * Maneja el caso cuando se excede el límite de sesiones.
     *
     * @param mixed $user
     * @param array $activeSessions
     * @param string $currentSessionId
     * @param Request $request
     * @return Response
     */
    protected function handleExcessSessions($user, array $activeSessions, string $currentSessionId, Request $request): Response
    {
        $cacheKey = "user_sessions:{$user->id}";

        switch ($this->strategy) {
            case 'block':
                // Bloquear nueva sesión
                Log::warning('Sesión bloqueada - Límite de sesiones concurrentes excedido', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'session_count' => count($activeSessions),
                    'max_sessions' => $this->maxSessions,
                    'ip_address' => $request->ip()
                ]);

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'message' => 'Has excedido el límite de sesiones concurrentes. Por favor, cierra otras sesiones e intenta nuevamente.',
                    'error' => 'MAX_SESSIONS_EXCEEDED',
                    'active_sessions' => count($activeSessions),
                    'max_sessions' => $this->maxSessions
                ], 429);

            case 'logout_oldest':
                // Cerrar sesión más antigua
                uasort($activeSessions, function ($a, $b) {
                    return strtotime($a['started_at']) <=> strtotime($b['started_at']);
                });

                // Remover la sesión más antigua
                $oldestSessionId = array_key_first($activeSessions);
                unset($activeSessions[$oldestSessionId]);

                // Actualizar caché
                Cache::put($cacheKey, $activeSessions, now()->addHours(2));

                Log::info('Sesión antigua cerrada automáticamente', [
                    'user_id' => $user->id,
                    'closed_session_id' => $oldestSessionId,
                    'remaining_sessions' => count($activeSessions)
                ]);

                // Continuar con la solicitud actual
                return $this->handle($request, function ($req) {
                    return response()->json([
                        'message' => 'Sesión iniciada. Una sesión antigua fue cerrada automáticamente.',
                        'warning' => 'OLDEST_SESSION_CLOSED'
                    ]);
                });

            case 'notify':
            default:
                // Solo notificar, permitir acceso
                Log::info('Múltiples sesiones concurrentes detectadas', [
                    'user_id' => $user->id,
                    'session_count' => count($activeSessions),
                    'sessions' => array_map(function ($session) {
                        return [
                            'ip' => $session['ip_address'],
                            'started_at' => $session['started_at'],
                            'last_activity' => $session['last_activity']
                        ];
                    }, $activeSessions)
                ]);

                // Actualizar caché y continuar
                Cache::put($cacheKey, $activeSessions, now()->addHours(2));
                
                // Agregar header de advertencia
                $request->attributes->set('session_warning', [
                    'type' => 'CONCURRENT_SESSIONS',
                    'message' => 'Tienes múltiples sesiones activas',
                    'count' => count($activeSessions)
                ]);

                break;
        }

        return response()->json(['status' => 'ok']);
    }
}
