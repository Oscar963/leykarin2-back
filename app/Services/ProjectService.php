<?php

namespace App\Services;

use App\Models\Project;
use App\Models\PurchasePlan;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Obtiene todos los proyectos de un plan de compra específico
     *
     * @param int $purchasePlanId ID del plan de compra
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProjectsByPlan(int $purchasePlanId)
    {
        return Project::where('purchase_plan_id', $purchasePlanId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Obtiene proyectos paginados filtrados por token del plan de compra
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @param string $token_purchase_plan Token del plan de compra
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllProjectsByToken(?string $query, int $perPage, string $token_purchase_plan)
    {
        $queryBuilder = Project::whereHas('purchasePlan', function ($q) use ($token_purchase_plan) {
            $q->where('token', $token_purchase_plan);
        })
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
        $purchasePlan = PurchasePlan::where('token', $data['token_purchase_plan'])->firstOrFail();

        $project = new Project();
        $project->name = $data['name'];
        $project->token = Str::random(32);
        $project->project_number = $purchasePlan->getNextProjectNumber();
        $project->description = $data['description'];
        $project->unit_purchasing_id = $data['unit_purchasing_id'];
        $project->type_project_id = $data['type_project_id'];
        $project->direction_id = $purchasePlan->direction->id;
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
}
