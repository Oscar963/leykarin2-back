<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para validar que el User-Agent sea consistente durante la sesión.
 * 
 * Este middleware ayuda a prevenir session hijacking al detectar cambios
 * en el User-Agent del navegador durante una sesión activa.
 */
class ValidateUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $sessionUserAgent = $request->session()->get('user_agent_hash');
            $currentUserAgent = $request->userAgent();
            $currentUserAgentHash = hash('sha256', $currentUserAgent ?? '');

            // Si ya existe un User-Agent registrado en la sesión
            if ($sessionUserAgent) {
                // Verificar si el User-Agent actual coincide con el registrado
                if ($sessionUserAgent !== $currentUserAgentHash) {
                    $user = Auth::user();

                    // Log de seguridad - posible session hijacking
                    Log::warning('User-Agent mismatch detectado - Posible session hijacking', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'session_user_agent' => $sessionUserAgent,
                        'current_user_agent_hash' => $currentUserAgentHash,
                        'ip_address' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'timestamp' => now()->toDateTimeString()
                    ]);

                    // Cerrar sesión por seguridad
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Retornar respuesta de sesión inválida
                    return response()->json([
                        'message' => 'Tu sesión ha sido invalidada por razones de seguridad. Por favor, inicia sesión nuevamente.',
                        'error' => 'SESSION_INVALID',
                        'reason' => 'USER_AGENT_MISMATCH'
                    ], 401);
                }
            } else {
                // Primera vez - registrar el User-Agent en la sesión
                $request->session()->put('user_agent_hash', $currentUserAgentHash);
                
                // También guardar información adicional para debugging
                $request->session()->put('user_agent_info', [
                    'original' => $currentUserAgent,
                    'registered_at' => now()->toDateTimeString(),
                    'ip_address' => $request->ip()
                ]);
            }
        }

        return $next($request);
    }
}
