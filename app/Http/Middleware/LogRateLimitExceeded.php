<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRateLimitExceeded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si la respuesta es 429 (Too Many Requests), loguear el evento
        if ($response->getStatusCode() === 429) {
            SecurityLogService::logRateLimitExceeded($request->route()->getName(), $request);
        }

        return $response;
    }
} 