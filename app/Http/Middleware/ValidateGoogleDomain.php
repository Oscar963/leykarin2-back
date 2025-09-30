<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateGoogleDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar en producción
        if (!app()->environment('production')) {
            return $next($request);
        }

        // Verificar que el dominio permitido esté configurado
        $allowedDomain = config('services.google.allowed_domain');
        
        if (empty($allowedDomain)) {
            Log::critical('GOOGLE_ALLOWED_DOMAIN no está configurado en producción', [
                'route' => $request->path(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'error' => 'Configuración de seguridad incompleta',
                'message' => 'El sistema no está configurado correctamente para autenticación OAuth.'
            ], 500);
        }

        // Verificar que auto-registro esté deshabilitado en producción
        if (config('services.google.auto_register', false)) {
            Log::warning('Auto-registro de Google OAuth habilitado en producción', [
                'route' => $request->path(),
                'ip' => $request->ip()
            ]);
        }

        return $next($request);
    }
}
