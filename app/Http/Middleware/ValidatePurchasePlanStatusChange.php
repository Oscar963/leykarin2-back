<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePurchasePlanStatusChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $statusId = $request->input('status_purchase_plan_id') ?? $request->input('status_id');

        // Si no hay status_id, continuar (puede ser otra operación)
        if (!$statusId) {
            return $next($request);
        }

        // Validar permisos según el rol y el estado
        if ($user->hasRole('Visador o de Administrador Municipal')) {
            // El Visador solo puede cambiar a estado 3 (Visado) o 5 (Rechazado)
            if (!in_array($statusId, [3, 5])) {
                return response()->json([
                    'message' => 'El Visador solo puede visar (estado 3) o rechazar (estado 5) planes de compra.'
                ], 403);
            }
        } elseif ($user->hasRole('Administrador Municipal')) {
            // El Administrador Municipal puede cambiar a cualquier estado excepto 6 y 7
            if (in_array($statusId, [6, 7])) {
                return response()->json([
                    'message' => 'El Administrador Municipal no puede decretar (estado 6) o publicar (estado 7) planes de compra.'
                ], 403);
            }
        } elseif ($user->hasRole('Administrador del Sistema')) {
            // El Administrador del Sistema puede cambiar a cualquier estado
            // No hay restricciones
        } elseif ($user->hasRole('Director') || $user->hasRole('Subrogante de Director')) {
            // El Director y Subrogante de Director solo pueden enviar planes (estado 2 - Enviado)
            if ($statusId != 2) {
                return response()->json([
                    'message' => 'El Director y Subrogante de Director solo pueden enviar planes de compra (estado 2).'
                ], 403);
            }
        } else {
            // Otros roles no pueden cambiar estados
            return response()->json([
                'message' => 'No tienes permisos para cambiar el estado de planes de compra.'
            ], 403);
        }

        return $next($request);
    }
} 