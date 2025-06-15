<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\PurchasePlan;
use App\Services\ProjectService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use LogsActivity;

    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Listar todos los proyectos de un plan de compra.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $token_purchase_plan = $request->query('token_purchase_plan');

            // Verificar si existe el plan de compra
            $purchasePlan = PurchasePlan::where('token', $token_purchase_plan)->first();

            if (!$purchasePlan) {
                return response()->json([
                    'message' => 'Plan de compra no encontrado'
                ], 404);
            }

            $projects = $this->projectService->getAllProjectsByQuery($query, $perPage);

            return response()->json([
                'data' => ProjectResource::collection($projects)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los proyectos.'
            ], 500);
        }
    }

    /**
     * Lista todos los proyectos de un plan de compra.
     */
    public function indexByPurchasePlan(int $purchasePlanId, Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');

            $projects = $this->projectService->getAllProjectsByPurchasePlan($purchasePlanId, $query, $perPage);

            return response()->json([
                'data' => ProjectResource::collection($projects)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los proyectos.'
            ], 500);
        }
    }

    /**
     * Mostrar un proyecto.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectById($id);

            return response()->json([
                'data' => new ProjectResource($project)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Proyecto no encontrado.'
            ], 404);
        }
    }

    public function showByToken(string $token): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectByToken($token);

            return response()->json([
                'data' => new ProjectResource($project)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Proyecto no encontrado.'
            ], 404);
        }
    }

    /**
     * Crear un proyecto.
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->projectService->createProject($request->validated());
            $this->logActivity('create_project', 'Usuario creó un proyecto con ID: ' . $project->id);

            return response()->json([
                'message' => 'Proyecto ha sido guardado exitosamente',
                'data' => new ProjectResource($project)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el proyecto. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un proyecto.
     */
    public function update(int $id, ProjectRequest $request): JsonResponse
    {
        try {
            $updated = $this->projectService->updateProject($id, $request->validated());
            $this->logActivity('update_project', 'Usuario actualizó el proyecto con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Proyecto ha sido actualizado exitosamente',
                'data' => new ProjectResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el proyecto. ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateByToken(string $token, ProjectRequest $request): JsonResponse
    {
        try {
            $updated = $this->projectService->updateProjectByToken($token, $request->validated());
            $this->logActivity('update_project', 'Usuario actualizó el proyecto con token: ' . $token);

            return response()->json([
                'message' => 'Proyecto ha sido actualizado exitosamente',
                'data' => new ProjectResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el proyecto. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un proyecto.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->projectService->deleteProject($id);
            $this->logActivity('delete_project', 'Usuario eliminó el proyecto con ID: ' . $id);

            return response()->json([
                'message' => 'Proyecto ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el proyecto. ' . $e->getMessage()
            ], 500);
        }
    }
}
