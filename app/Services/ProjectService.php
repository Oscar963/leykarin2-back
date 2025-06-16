<?php

namespace App\Services;

use App\Models\File;
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
}
