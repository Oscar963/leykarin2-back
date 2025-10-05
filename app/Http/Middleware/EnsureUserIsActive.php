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
        $user = $request->user();
        
        // Verifica si el usuario está autenticado y si su estado es inactivo (false o 0)
        if ($user && !$user->status) {
            // Para API con Sanctum, revocar el token actual
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            // Retorna una respuesta de error 403 (Forbidden)
            return response()->json([
                'message' => 'Tu cuenta ha sido suspendida. Por favor contacta al administrador.'
            ], 403);
        }

        // Si el usuario está activo, permite que la petición continúe.
        return $next($request);
    }
}
