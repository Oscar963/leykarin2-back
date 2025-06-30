<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanSendPurchasePlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Verificar si el usuario tiene uno de los roles permitidos
        $allowedRoles = [
            'Administrador del Sistema',
            'Administrador Municipal',
            'Visador o de Administrador Municipal',
            'Director',
            'Subrogante de Director'
        ];

        if (!$user->hasAnyRole($allowedRoles)) {
            return response()->json([
                'message' => 'Solo los administradores del sistema, administradores municipales, directores y subrogantes de director pueden enviar planes de compra para aprobación.',
                'user_roles' => $user->getRoleNames()->toArray(),
                'allowed_roles' => $allowedRoles
            ], 403);
        }

        return $next($request);
    }
}
