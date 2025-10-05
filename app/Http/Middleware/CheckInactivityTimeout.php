<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para cerrar sesión automáticamente después de un período de inactividad.
 * 
 * Este middleware verifica el tiempo transcurrido desde la última actividad del usuario
 * y cierra la sesión si excede el timeout configurado.
 */
class CheckInactivityTimeout
{
    /**
     * Tiempo de inactividad permitido en segundos.
     * Por defecto: 1800 segundos (30 minutos)
     * 
     * @var int
     */
    protected $timeout;

    public function __construct()
    {
        // Obtener timeout de configuración o usar 30 minutos por defecto
        $this->timeout = config('session.inactivity_timeout', 1800);
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
            $lastActivity = $request->session()->get('last_activity_time');
            $currentTime = time();

            // Si existe un registro de última actividad
            if ($lastActivity) {
                $inactiveTime = $currentTime - $lastActivity;

                // Si el tiempo de inactividad excede el timeout
                if ($inactiveTime > $this->timeout) {
                    // Cerrar sesión
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Retornar respuesta de sesión expirada
                    return response()->json([
                        'message' => 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.',
                        'error' => 'SESSION_EXPIRED',
                        'inactive_time' => $inactiveTime,
                        'timeout' => $this->timeout
                    ], 401);
                }
            }

            // Actualizar el timestamp de última actividad
            $request->session()->put('last_activity_time', $currentTime);
        }

        return $next($request);
    }
}
