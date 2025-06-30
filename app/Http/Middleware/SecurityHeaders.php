<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir ataques de clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevenir ataques XSS
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevenir MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy para privacidad
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (anteriormente Feature Policy)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()'
        );

        // Content Security Policy para prevenir XSS y ataques de inyección
        if (app()->environment('production')) {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Para desarrollo, restringir en producción
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com",
                "font-src 'self' fonts.gstatic.com",
                "img-src 'self' data: https:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'"
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // Strict Transport Security (solo en HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remover headers que revelan información del servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
