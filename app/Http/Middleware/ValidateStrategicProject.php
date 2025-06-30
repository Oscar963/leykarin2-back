<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Goal;
use Symfony\Component\HttpFoundation\Response;

class ValidateStrategicProject
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo validar en rutas de metas
        if (!$request->routeIs('goals.*')) {
            return $next($request);
        }

        // Para crear o actualizar metas, validar que el proyecto sea estratégico
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $projectId = null;

            // Si es creación, obtener project_id del request
            if ($request->isMethod('POST') && $request->has('project_id')) {
                $projectId = $request->input('project_id');
            }
            // Si es actualización, obtener project_id de la meta existente
            elseif (in_array($request->method(), ['PUT', 'PATCH'])) {
                $goalId = $request->route('goal');
                if ($goalId) {
                    $goal = Goal::find($goalId);
                    if ($goal) {
                        $projectId = $goal->project_id;
                    }
                }
            }

            if ($projectId) {
                $project = Project::with('typeProject')->find($projectId);

                if (!$project) {
                    return response()->json([
                        'message' => 'Proyecto no encontrado',
                        'error' => 'El proyecto especificado no existe'
                    ], 404);
                }

                if (!$project->isStrategic()) {
                    return response()->json([
                        'message' => 'Proyecto no válido para metas',
                        'error' => 'Solo se pueden crear metas en proyectos de tipo estratégico. Este proyecto es de tipo: ' . ($project->typeProject->name ?? 'no definido')
                    ], 422);
                }
            }
        }

        // Para visualizar metas, validar que el proyecto asociado sea estratégico
        if ($request->isMethod('GET') && $request->route('goal')) {
            $goalId = $request->route('goal');
            $goal = Goal::with('project.typeProject')->find($goalId);

            if ($goal && !$goal->project->isStrategic()) {
                return response()->json([
                    'message' => 'Acceso denegado',
                    'error' => 'Esta meta pertenece a un proyecto que no es estratégico'
                ], 403);
            }
        }

        return $next($request);
    }
}
