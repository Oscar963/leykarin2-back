<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para gestión de Metas de Proyectos Estratégicos
 */
class GoalService
{
    /**
     * Obtiene todas las metas con filtros opcionales
     */
    public function getAllGoals($projectId = null, $query = null, $status = null, $perPage = 10)
    {
        $queryBuilder = Goal::with(['project.typeProject', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc');

        // Filtrar por proyecto específico
        if ($projectId) {
            $queryBuilder->where('project_id', $projectId);
        }

        // Solo metas de proyectos estratégicos
        $queryBuilder->whereHas('project.typeProject', function ($q) {
            $q->where('name', 'Estratégico');
        });

        // Filtrar por búsqueda en nombre o descripción
        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        // Filtrar por estado
        if ($status) {
            $queryBuilder->where('status', $status);
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene una meta por su ID
     */
    public function getGoalById($id)
    {
        $goal = Goal::with(['project.typeProject', 'createdBy', 'updatedBy'])->find($id);

        if (!$goal) {
            throw new \Exception('Meta no encontrada');
        }

        return $goal;
    }

    /**
     * Crea una nueva meta
     */
    public function createGoal(array $data)
    {
        // Verificar que el proyecto sea estratégico
        $project = Project::with('typeProject')->find($data['project_id']);

        if (!$project) {
            throw new \Exception('Proyecto no encontrado');
        }

        if (!$project->isStrategic()) {
            throw new \Exception('Solo se pueden crear metas en proyectos estratégicos');
        }

        DB::beginTransaction();

        try {
            $goal = new Goal();
            $goal->name = $data['name'];
            $goal->description = $data['description'] ?? null;
            $goal->target_value = $data['target_value'] ?? null;
            $goal->unit_measure = $data['unit_measure'] ?? null;
            $goal->target_date = $data['target_date'] ?? null;
            $goal->notes = $data['notes'] ?? null;
            $goal->project_id = $project->id;
            $goal->created_by = auth()->id();
            $goal->save();

            DB::commit();

            return $goal->load(['project.typeProject', 'createdBy', 'updatedBy']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al crear la meta: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una meta existente
     */
    public function updateGoal($id, array $data)
    {
        $goal = $this->getGoalById($id);

        DB::beginTransaction();

        try {
            $goal->name = $data['name'];
            $goal->description = $data['description'] ?? $goal->description;
            $goal->target_value = $data['target_value'] ?? $goal->target_value;
            $goal->unit_measure = $data['unit_measure'] ?? $goal->unit_measure;
            $goal->target_date = $data['target_date'] ?? $goal->target_date;
            $goal->status = $data['status'] ?? $goal->status;
            $goal->notes = $data['notes'] ?? $goal->notes;
            $goal->updated_by = auth()->id();
            $goal->save();

            DB::commit();

            return $goal->load(['project.typeProject', 'createdBy', 'updatedBy']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al actualizar la meta: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una meta
     */
    public function deleteGoal($id)
    {
        $goal = $this->getGoalById($id);

        DB::beginTransaction();

        try {
            $goal->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar la meta: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el progreso de una meta
     */
    public function updateGoalProgress($id, $currentValue, $notes = null)
    {
        $goal = $this->getGoalById($id);

        DB::beginTransaction();

        try {
            $goal->updateProgress($currentValue, $notes);
            DB::commit();

            return $goal->load(['project.typeProject', 'createdBy', 'updatedBy']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al actualizar el progreso: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas de metas por proyecto
     */
    public function getProjectGoalStatistics($projectId)
    {
        $project = Project::with('typeProject')->find($projectId);

        if (!$project) {
            throw new \Exception('Proyecto no encontrado');
        }

        if (!$project->isStrategic()) {
            throw new \Exception('Solo los proyectos estratégicos pueden tener estadísticas de metas');
        }

        $goals = Goal::where('project_id', $projectId)->get();

        $statistics = [
            'project_id' => $projectId,
            'project_name' => $project->name,
            'project_type' => $project->typeProject->name,
            'total_goals' => $goals->count(),
            'completed_goals' => $goals->where('status', Goal::STATUS_COMPLETED)->count(),
            'in_progress_goals' => $goals->where('status', Goal::STATUS_IN_PROGRESS)->count(),
            'pending_goals' => $goals->where('status', Goal::STATUS_PENDING)->count(),
            'cancelled_goals' => $goals->where('status', Goal::STATUS_CANCELLED)->count(),
            'overdue_goals' => $goals->filter(function ($goal) {
                return $goal->isOverdue();
            })->count(),
            'average_progress' => $project->getAverageGoalProgress(),
            'completion_percentage' => $goals->count() > 0 
                ? round(($goals->where('status', Goal::STATUS_COMPLETED)->count() / $goals->count()) * 100, 2) 
                : 0
        ];

        return $statistics;
    }

    /**
     * Obtiene metas vencidas
     */
    public function getOverdueGoals($projectId = null, $perPage = 10)
    {
        $queryBuilder = Goal::overdue()
            ->with(['project.typeProject', 'createdBy', 'updatedBy'])
            ->orderBy('target_date', 'asc');

        if ($projectId) {
            $queryBuilder->where('project_id', $projectId);
        }

        // Solo metas de proyectos estratégicos
        $queryBuilder->whereHas('project.typeProject', function ($q) {
            $q->where('name', 'Estratégico');
        });

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene metas próximas a vencer (en los próximos N días)
     */
    public function getUpcomingGoals($days = 7, $projectId = null, $perPage = 10)
    {
        $queryBuilder = Goal::where('target_date', '>=', now())
            ->where('target_date', '<=', now()->addDays($days))
            ->whereNotIn('status', [Goal::STATUS_COMPLETED, Goal::STATUS_CANCELLED])
            ->with(['project.typeProject', 'createdBy', 'updatedBy'])
            ->orderBy('target_date', 'asc');

        if ($projectId) {
            $queryBuilder->where('project_id', $projectId);
        }

        // Solo metas de proyectos estratégicos
        $queryBuilder->whereHas('project.typeProject', function ($q) {
            $q->where('name', 'Estratégico');
        });

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene resumen general de metas por estado
     */
    public function getGoalsSummary($projectId = null)
    {
        $queryBuilder = Goal::strategic();

        if ($projectId) {
            $queryBuilder->where('project_id', $projectId);
        }

        $goals = $queryBuilder->get();

        return [
            'total' => $goals->count(),
            'by_status' => [
                'pending' => $goals->where('status', Goal::STATUS_PENDING)->count(),
                'in_progress' => $goals->where('status', Goal::STATUS_IN_PROGRESS)->count(),
                'completed' => $goals->where('status', Goal::STATUS_COMPLETED)->count(),
                'cancelled' => $goals->where('status', Goal::STATUS_CANCELLED)->count(),
            ],
            'overdue' => $goals->filter(function ($goal) {
                return $goal->isOverdue();
            })->count(),
            'average_progress' => $goals->count() > 0 
                ? $goals->avg(function ($goal) {
                    return $goal->getProgressPercentage();
                })
                : 0
        ];
    }
} 