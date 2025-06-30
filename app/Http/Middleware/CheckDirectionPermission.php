<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDirectionPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        // Si el usuario es administrador del sistema, permitir todo
        if ($user->hasRole('Administrador del Sistema')) {
            return $next($request);
        }

        // Si el usuario es administrador municipal, permitir todo
        if ($user->hasRole('Administrador Municipal')) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso específico
        if (!$user->can($permission)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        // Si la acción está relacionada con una dirección específica
        if ($request->has('direction_id') || $request->route('direction')) {
            $directionId = $request->input('direction_id') ?? $request->route('direction');

            // Verificar si el usuario pertenece a esa dirección
            if (!$user->directions()->where('direction_id', $directionId)->exists()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder a esta dirección'
                ], 403);
            }
        }

        return $next($request);
    }
}
