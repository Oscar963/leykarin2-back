<?php

namespace App\Services;

use App\Models\File;
use App\Models\Goal;
use App\Models\Project;
use App\Models\PurchasePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectService
{
    /**
     * Obtiene todos los proyectos con paginación y filtrado por nombre y descripción
     */
    public function getAllProjectsByQuery(?string $query, int $perPage = 50)
    {
        $queryBuilder = Project::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }


    /**
     * Obtiene todos los proyectos de un plan de compra paginados y filtrados por nombre y descripción
     */
    public function getAllProjectsByPurchasePlan(int $purchasePlanId, ?string $query = null, int $perPage = 50)
    {
        $queryBuilder = Project::where('purchase_plan_id', $purchasePlanId)
            ->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un proyecto por su ID
     *
     * @param int $id ID del proyecto
     * @return Project
     */
    public function getProjectById(int $id)
    {
        return Project::findOrFail($id);
    }

    /**
     * Obtiene un proyecto por su token
     *
     * @param string $token Token del proyecto
     * @return Project
     */
    public function getProjectByToken(string $token)
    {
        return Project::where('token', $token)->firstOrFail();
    }

    /**
     * Crea un nuevo proyecto
     *
     * @param array $data Datos del proyecto
     * @return Project
     */
    public function createProject(array $data)
    {
        $purchasePlan = PurchasePlan::where('id', $data['purchase_plan_id'])->firstOrFail();

        $project = new Project();
        $project->name = $data['name'];
        $project->token = Str::random(32);
        $project->project_number = $purchasePlan->getNextProjectNumber();
        $project->created_by = auth()->user()->id;
        $project->description = $data['description'];
        $project->unit_purchasing_id = $data['unit_purchasing_id'];
        $project->type_project_id = $data['type_project_id'];
        $project->purchase_plan_id = $purchasePlan->id;
        $project->save();

        // Guardar las metas si el proyecto es estratégico y se enviaron metas
        if (isset($data['goals']) && is_array($data['goals']) && !empty($data['goals'])) {
            $this->saveProjectGoals($project, $data['goals']);
        }

        return $project;
    }

    /**
     * Actualiza un proyecto existente por su ID
     *
     * @param int $id ID del proyecto
     * @param array $data Datos actualizados
     * @return Project
     */
    public function updateProject(int $id, array $data)
    {
        $project = $this->getProjectById($id);
        $project->name = $data['name'];
        $project->description = $data['description'];
        $project->unit_purchasing_id = $data['unit_purchasing_id'];
        $project->type_project_id = $data['type_project_id'];
        $project->updated_by = auth()->user()->id;
        $project->save();

        // Actualizar las metas si se enviaron
        if (isset($data['goals']) && is_array($data['goals'])) {
            $this->updateProjectGoals($project, $data['goals']);
        }

        return $project;
    }

    /**
     * Actualiza un proyecto existente por su token
     *
     * @param string $token Token del proyecto
     * @param array $data Datos actualizados
     * @return Project
     */
    public function updateProjectByToken(string $token, array $data)
    {
        $project = $this->getProjectByToken($token);
        $project->name = $data['name'];
        $project->description = $data['description'];
        $project->unit_purchasing_id = $data['unit_purchasing_id'];
        $project->type_project_id = $data['type_project_id'];
        $project->updated_by = auth()->user()->id;
        $project->save();

        // Actualizar las metas si se enviaron
        if (isset($data['goals']) && is_array($data['goals'])) {
            $this->updateProjectGoals($project, $data['goals']);
        }

        return $project;
    }

    /**
     * Elimina un proyecto y reorganiza los números de proyecto restantes
     *
     * @param int $id ID del proyecto
     * @return void
     */
    public function deleteProject(int $id)
    {
        $project = Project::findOrFail($id);
        $purchasePlanId = $project->purchase_plan_id;

        $project->delete();

        $this->reorderProjectNumbers($purchasePlanId);
    }

    /**
     * Reordena los números de proyecto secuencialmente
     *
     * @param int $purchasePlanId ID del plan de compra
     * @return void
     */
    private function reorderProjectNumbers(int $purchasePlanId): void
    {
        $projects = Project::where('purchase_plan_id', $purchasePlanId)
            ->orderBy('project_number', 'ASC')
            ->get();

        $projectNumber = 1;
        foreach ($projects as $project) {
            $project->project_number = $projectNumber;
            $project->save();
            $projectNumber++;
        }
    }

    /**
     * Verifica si el proyecto ya existe en la base de datos
     */
    public function verification(array $data)
    {
        $project = $this->getProjectById($data['project_id']);

        $fileService = new FileService();
        $originalName = $data['file']->getClientOriginalName();
        $data['name'] = pathinfo($originalName, PATHINFO_FILENAME);
        $data['description'] = 'Verificación del proyecto ' . $project->name . ' - ' . $project->project_number;
        $data['file'] = $data['file'];
        $folder = 'proyectos/verificaciones/' . $project->id;
        $data['folder'] = $folder;

        // Agregar datos de la relación polimórfica
        $data['fileable_type'] = Project::class;
        $data['fileable_id'] = $project->id;

        $fileService->createFile($data);

        return $project;
    }

    public function downloadFileVerificationProject(int $fileId): BinaryFileResponse
    {
        $file = File::findOrFail($fileId);
        $filePath = str_replace(url('storage/'), '', $file->url);
        return response()->download(storage_path("app/public/{$filePath}"), $file->name);
    }

    /**
     * Guarda las metas de un proyecto
     *
     * @param Project $project Proyecto al que se le asignarán las metas
     * @param array $goals Array de metas a guardar
     * @return void
     * @throws \Exception
     */
    private function saveProjectGoals(Project $project, array $goals): void
    {
        // Verificar que el proyecto sea estratégico antes de guardar metas
        if (!$project->isStrategic()) {
            throw new \Exception('Solo se pueden crear metas en proyectos de tipo estratégico. Este proyecto es de tipo: ' . ($project->typeProject->name ?? 'no definido'));
        }

        foreach ($goals as $goalData) {
            // Validar que los campos requeridos estén presentes
            if (empty($goalData['name']) || empty($goalData['description'])) {
                continue; // Saltar metas incompletas
            }

            $goal = new Goal();
            $goal->name = $goalData['name'];
            $goal->description = $goalData['description'];
            $goal->target_value = !empty($goalData['target_value']) ? $goalData['target_value'] : null;
            $goal->progress_value = !empty($goalData['progress_value']) ? $goalData['progress_value'] : 0;
            $goal->unit_measure = !empty($goalData['unit_measure']) ? $goalData['unit_measure'] : null;
            $goal->target_date = !empty($goalData['target_date']) ? $goalData['target_date'] : null;
            $goal->notes = !empty($goalData['notes']) ? $goalData['notes'] : null;
            $goal->current_value = 0; // Mantenemos por compatibilidad
            $goal->status = Goal::STATUS_PENDING; // Estado inicial
            $goal->project_id = $project->id;
            $goal->created_by = auth()->user()->id;
            $goal->save();
        }
    }

    /**
     * Actualiza las metas de un proyecto
     *
     * @param Project $project Proyecto cuyas metas se actualizarán
     * @param array $goals Array de metas a actualizar
     * @return void
     */
    private function updateProjectGoals(Project $project, array $goals): void
    {
        // Eliminar las metas existentes del proyecto
        $project->goals()->delete();

        // Crear las nuevas metas
        if (!empty($goals)) {
            $this->saveProjectGoals($project, $goals);
        }
    }
}
