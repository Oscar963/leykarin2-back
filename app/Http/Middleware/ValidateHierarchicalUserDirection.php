<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ValidateHierarchicalUserDirection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo validar en requests que modifiquen usuarios o direcciones
        if (!$this->shouldValidateRequest($request)) {
            return $next($request);
        }

        $user = auth()->user();

        // Si el usuario es administrador del sistema, permitir todo
        if ($user && $user->hasRole('Administrador del Sistema')) {
            return $next($request);
        }

        // Validar asignación de direcciones a usuarios
        if ($request->isMethod('POST') && $request->routeIs('directions.assign-users')) {
            return $this->validateUserDirectionAssignment($request, $next);
        }

        // Validar creación/edición de usuarios
        if ($request->isMethod(['POST', 'PUT', 'PATCH']) && $request->routeIs('users.*')) {
            return $this->validateUserCreation($request, $next);
        }

        return $next($request);
    }

    /**
     * Determina si el request debe ser validado
     */
    private function shouldValidateRequest(Request $request): bool
    {
        $validRoutes = [
            'directions.assign-users',
            'directions.assign-director',
            'users.store',
            'users.update'
        ];

        return $request->routeIs($validRoutes);
    }

    /**
     * Valida la asignación de direcciones a usuarios
     */
    private function validateUserDirectionAssignment(Request $request, Closure $next): Response
    {
        $userIds = $request->input('user_ids', []);
        $directionId = $request->route('direction');

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if (!$user) {
                continue;
            }

            // Verificar si el usuario tiene roles jerárquicos (excluyendo administradores y secretaría comunal)
            if ($user->hasHierarchicalRole() && !$user->hasAnyRole(User::MULTI_DIRECTION_ROLES)) {
                // Verificar si ya pertenece a otra dirección
                $currentDirections = $user->directions()->where('direction_id', '!=', $directionId)->count();

                if ($currentDirections > 0) {
                    return response()->json([
                        'message' => "El usuario {$user->name} {$user->paternal_surname} tiene roles jerárquicos y ya pertenece a otra dirección. Los usuarios con roles jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura) solo pueden pertenecer a una dirección. Los administradores y encargados de presupuestos pueden tener múltiples direcciones.",
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'paternal_surname' => $user->paternal_surname,
                            'roles' => $user->getRoleNames()->toArray()
                        ]
                    ], 422);
                }
            }
        }

        return $next($request);
    }

    /**
     * Valida la creación/edición de usuarios
     */
    private function validateUserCreation(Request $request, Closure $next): Response
    {
        $roles = $request->input('roles', []);
        $directionIds = $request->input('direction_ids', []);

        // Verificar si se están asignando roles jerárquicos (excluyendo administradores y secretaría comunal)
        $hierarchicalRoles = array_intersect($roles, User::HIERARCHICAL_ROLES);
        $multiDirectionRoles = array_intersect($roles, User::MULTI_DIRECTION_ROLES);

        // Si tiene roles jerárquicos pero NO tiene roles de múltiples direcciones
        if (!empty($hierarchicalRoles) && empty($multiDirectionRoles) && count($directionIds) > 1) {
            return response()->json([
                'message' => 'Los usuarios con roles jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura) solo pueden pertenecer a una dirección. Los administradores y encargados de presupuestos pueden tener múltiples direcciones.',
                'hierarchical_roles' => $hierarchicalRoles,
                'assigned_directions' => count($directionIds)
            ], 422);
        }

        return $next($request);
    }
}
