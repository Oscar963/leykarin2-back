<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'target_value' => $this->target_value,
            'unit_measure' => $this->unit_measure,
            'current_value' => $this->current_value,
            'target_date' => $this->target_date ? $this->target_date->format('Y-m-d') : null,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'notes' => $this->notes,
            'progress_percentage' => $this->getProgressPercentage(),
            'is_completed' => $this->isCompleted(),
            'is_overdue' => $this->isOverdue(),
            'days_remaining' => $this->getDaysRemaining(),
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'type' => $this->project->typeProject->name ?? null,
                'purchase_plan_id' => $this->project->purchase_plan_id
            ],
            'created_by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'paternal_surname' => $this->createdBy->paternal_surname
            ],
            'updated_by' => $this->when($this->updatedBy, [
                'id' => optional($this->updatedBy)->id,
                'name' => optional($this->updatedBy)->name,
                'paternal_surname' => optional($this->updatedBy)->paternal_surname
            ]),
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null
        ];
    }

    /**
     * Obtiene la etiqueta legible del estado
     */
    private function getStatusLabel()
    {
        $labels = [
            'pendiente' => 'Pendiente',
            'en_progreso' => 'En Progreso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada'
        ];

        return $labels[$this->status] ?? $this->status;
    }
} 