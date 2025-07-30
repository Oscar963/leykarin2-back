<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ClaveUnicaValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar validaciones en el callback de Clave Única
        if (!$request->routeIs('auth.claveunica.callback')) {
            return $next($request);
        }

        // Validar que la request viene de Clave Única
        if (!$this->isValidClaveUnicaRequest($request)) {
            Log::warning('Clave Única: Request inválida detectada', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'params' => $request->all()
            ]);
            
            return redirect(config('app.frontend_url') . '/login?error=invalid_request');
        }

        // Validar parámetros OAuth2 requeridos
        if (!$this->hasRequiredOAuthParams($request)) {
            Log::warning('Clave Única: Parámetros OAuth2 faltantes', [
                'params' => $request->all()
            ]);
            
            return redirect(config('app.frontend_url') . '/login?error=missing_params');
        }

        return $next($request);
    }

    /**
     * Valida que la request provenga de Clave Única
     */
    private function isValidClaveUnicaRequest(Request $request): bool
    {
        // Verificar que tenga parámetros típicos de OAuth2
        $hasOAuthParams = $request->has(['code', 'state']) || $request->has('error');
        
        if (!$hasOAuthParams) {
            return false;
        }

        // Verificar referer (opcional, puede ser útil para seguridad adicional)
        $referer = $request->header('referer');
        if ($referer && !$this->isValidReferer($referer)) {
            Log::info('Clave Única: Referer no válido', ['referer' => $referer]);
            // No bloquear por referer inválido, solo logear
        }

        return true;
    }

    /**
     * Verifica que el referer sea de dominios válidos de Clave Única
     */
    private function isValidReferer(string $referer): bool
    {
        $validDomains = [
            'claveunica.gob.cl',
            'accounts.claveunica.gob.cl',
            'www.claveunica.gob.cl',
            // Agregar dominios de testing si es necesario
            'testing.claveunica.gob.cl',
        ];

        foreach ($validDomains as $domain) {
            if (str_contains($referer, $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica que estén presentes los parámetros OAuth2 necesarios
     */
    private function hasRequiredOAuthParams(Request $request): bool
    {
        // Si hay un error OAuth2, es válido
        if ($request->has('error')) {
            return true;
        }

        // Para flujo exitoso, debe tener code y state
        return $request->has(['code', 'state']);
    }
}
