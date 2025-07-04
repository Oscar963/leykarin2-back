<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateCookies
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
        $response = $next($request);

        // Solo procesar si es una respuesta HTTP
        if (!$response instanceof Response) {
            return $response;
        }

        // Obtener todas las cookies de la respuesta
        $cookies = $response->headers->getCookies();
        
        // Array para rastrear cookies únicas
        $uniqueCookies = [];
        $duplicateCookies = [];

        foreach ($cookies as $cookie) {
            $cookieKey = $cookie->getName() . '|' . $cookie->getDomain() . '|' . $cookie->getPath();
            
            if (isset($uniqueCookies[$cookieKey])) {
                // Cookie duplicada encontrada
                $duplicateCookies[] = $cookie;
            } else {
                $uniqueCookies[$cookieKey] = $cookie;
            }
        }

        // Si hay cookies duplicadas, limpiar y reestablecer solo las únicas
        if (!empty($duplicateCookies)) {
            Log::warning('Cookies duplicadas detectadas y removidas', [
                'duplicates' => array_map(function($cookie) {
                    return [
                        'name' => $cookie->getName(),
                        'domain' => $cookie->getDomain(),
                        'path' => $cookie->getPath()
                    ];
                }, $duplicateCookies)
            ]);

            // Limpiar todas las cookies de la respuesta
            $response->headers->remove('Set-Cookie');
            
            // Reestablecer solo las cookies únicas
            foreach ($uniqueCookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }
} 