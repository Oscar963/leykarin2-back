<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica si el usuario está autenticado y si su estado es inactivo (false o 0)
        if ($request->user() && !$request->user()->status) {

            // 1. Cierra la sesión del usuario en el backend
            Auth::guard('web')->logout(); // Especificamos el guard 'web' para cerrar la sesión

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // 2. Retorna una respuesta de error 403 (Forbidden)
            // El frontend puede usar este código para redirigir al login.
            return response()->json([
                'status' => 403,
                'error' => [
                    'message' => 'Tu cuenta ha sido suspendida. Sesión cerrada.'
                ]
            ], 403);
        }

        // Si el usuario está activo, permite que la petición continúe.
        return $next($request);
    }
}
