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
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'modification_type_id' => $this->modification_type_id,
            'purchase_plan_id' => $this->purchase_plan_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relación con tipo de modificación
            'modification_type' => $this->whenLoaded('modificationType', function () {
                return [
                    'id' => $this->modificationType->id,
                    'name' => $this->modificationType->name,
                    'description' => $this->modificationType->description
                ];
            }),
            
            // Relación con plan de compra
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
            
            // Usuario que creó la modificación
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email
                ];
            }),
            
            // Métodos de estado
            'is_active' => $this->isActive(),
            'is_pending' => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
            'is_inactive' => $this->isInactive(),
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
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado'
        ];

        return $statuses[$this->status] ?? $this->status;
    }
} 