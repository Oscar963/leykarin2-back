<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'modification_number' => $this->modification_number,
            'date' => $this->date->format('Y-m-d'),
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'purchase_plan_id' => $this->purchase_plan_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relaciones cargadas
            'purchase_plan' => $this->whenLoaded('purchasePlan', function () {
                return [
                    'id' => $this->purchasePlan->id,
                    'name' => $this->purchasePlan->name,
                    'year' => $this->purchasePlan->year,
                    'direction' => $this->whenLoaded('purchasePlan.direction', function () {
                        return [
                            'id' => $this->purchasePlan->direction->id,
                            'name' => $this->purchasePlan->direction->name
                        ];
                    })
                ];
            }),
            
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email
                ];
            }),
            
            'updated_by_user' => $this->whenLoaded('updatedBy', function () {
                return [
                    'id' => $this->updatedBy->id,
                    'name' => $this->updatedBy->name,
                    'email' => $this->updatedBy->email
                ];
            }),
            
            'history' => $this->whenLoaded('history', function () {
                return $this->history->map(function ($historyItem) {
                    return [
                        'id' => $historyItem->id,
                        'action' => $historyItem->action,
                        'action_label' => $this->getActionLabel($historyItem->action),
                        'description' => $historyItem->description,
                        'details' => $historyItem->details,
                        'date' => $historyItem->date->format('Y-m-d H:i:s'),
                        'user' => $historyItem->whenLoaded('user', function () use ($historyItem) {
                            return [
                                'id' => $historyItem->user->id,
                                'name' => $historyItem->user->name,
                                'email' => $historyItem->user->email
                            ];
                        })
                    ];
                });
            }),
            
            // Métodos de estado
            'is_active' => $this->isActive(),
            'is_pending' => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
        ];
    }

    /**
     * Obtiene la etiqueta del estado
     *
     * @return string
     */
    private function getStatusLabel(): string
    {
        $statuses = [
            'active' => 'Activa',
            'inactive' => 'Inactiva',
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Obtiene la etiqueta de la acción
     *
     * @param string $action
     * @return string
     */
    private function getActionLabel(string $action): string
    {
        $actions = [
            'create' => 'Crear',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
            'status_change' => 'Cambio de Estado'
        ];

        return $actions[$action] ?? $action;
    }
} 