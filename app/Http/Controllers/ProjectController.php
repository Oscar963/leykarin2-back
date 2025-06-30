<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\VerificationRequest;
use App\Models\PurchasePlan;
use App\Http\Resources\FileResource;
use App\Services\ProjectService;
use App\Exports\ProjectsWordExport;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * Mostrar un proyecto por token.
     */
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

    /**
     * Verifica si el proyecto ya existe en la base de datos
     */
    public function verification(VerificationRequest $request): JsonResponse
    {
        try {
            $project =  $this->projectService->verification($request->validated());
            $this->logActivity('verification_project', 'Usuario verificó el proyecto con ID: ' . $project->id);

            return response()->json([
                'message' => 'Se ha subido el archivo correctamente los archivos de verificación',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al subir el archivo. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un proyecto de verificación.
     */
    public function showVerificationProject(int $projectId): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectById($projectId);
            $mediaVerifiers = $project->mediaVerifiers()->orderBy('id', 'DESC')->get();
            $this->logActivity('list_media_verifiers', 'Usuario listó los medios verificadores del proyecto con ID: ' . $projectId);

            return response()->json([
                'data' => FileResource::collection($mediaVerifiers)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al listar los medios verificadores del proyecto con ID: ' . $projectId . ' . ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un proyecto de verificación.
     */
    public function deleteVerificationProject(int $projectId, int $fileId): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectById($projectId); // TODO: Verificar si el proyecto existe
            $file = $project->mediaVerifiers()->findOrFail($fileId);

            // Eliminar el archivo físico
            if (Storage::disk('public')->exists(str_replace(url('storage/'), '', $file->url))) {
                Storage::disk('public')->delete(str_replace(url('storage/'), '', $file->url));
            }

            // Eliminar el registro de la base de datos
            $file->delete();

            $this->logActivity('delete_media_verifier', 'Usuario eliminó el medio verificador con ID: ' . $fileId . ' del proyecto con ID: ' . $projectId);

            return response()->json([
                'message' => 'Medio verificador eliminado correctamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el medio verificador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar un archivo por ID. 
     */
    public function downloadVerificationProject(int $fileId): BinaryFileResponse
    {
        return $this->projectService->downloadFileVerificationProject($fileId);
    }

    /**
     * Exportar proyectos a Word por plan de compra.
     */
    public function exportWord($purchasePlanId)
    {
        try {
            $this->logActivity('download_projects_word_file', 'Usuario exportó el documento Word de proyectos para el plan de compra: ' . $purchasePlanId);
            return (new ProjectsWordExport($purchasePlanId))->export();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al exportar el documento Word: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene un dashboard con estadísticas de metas de proyectos estratégicos
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $purchasePlanId = $request->query('purchase_plan_id');
            
            $query = $this->projectService->getAllProjectsByPurchasePlan($purchasePlanId, null, 1000);
            $strategicProjects = $query->where('type_project_id', 2); // Asumiendo que ID 2 es estratégico
            
            $totalStrategicProjects = $strategicProjects->count();
            $totalGoals = 0;
            $completedGoals = 0;
            $atRiskGoals = 0;
            $totalProgress = 0;
            $projectsWithGoals = 0;

            foreach ($strategicProjects as $project) {
                $stats = $project->getGoalStatistics();
                if ($stats['total_goals'] > 0) {
                    $totalGoals += $stats['total_goals'];
                    $completedGoals += $stats['completed_goals'];
                    $atRiskGoals += $stats['at_risk_goals'];
                    $totalProgress += $stats['average_progress'];
                    $projectsWithGoals++;
                }
            }

            $averageProgress = $projectsWithGoals > 0 ? round($totalProgress / $projectsWithGoals, 2) : 0;
            $completionRate = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0;

            return response()->json([
                'message' => 'Dashboard de metas obtenido exitosamente',
                'data' => [
                    'general_statistics' => [
                        'total_strategic_projects' => $totalStrategicProjects,
                        'projects_with_goals' => $projectsWithGoals,
                        'total_goals' => $totalGoals,
                        'completed_goals' => $completedGoals,
                        'at_risk_goals' => $atRiskGoals,
                        'average_progress' => $averageProgress,
                        'completion_rate' => $completionRate
                    ],
                    'projects' => ProjectResource::collection($strategicProjects)
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el dashboard de metas: ' . $e->getMessage()
            ], 500);
        }
    }
}
