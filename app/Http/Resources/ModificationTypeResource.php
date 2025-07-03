<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModificationTypeResource extends JsonResource
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Estadísticas básicas
            'modifications_count' => $this->whenLoaded('modifications', function () {
                return $this->modifications->count();
            }),
            
            // Relaciones cargadas
            'modifications' => $this->whenLoaded('modifications', function () {
                return ModificationResource::collection($this->modifications);
            }),
            
            // Métodos de utilidad
            'is_in_use' => $this->isInUse(),
            'statistics' => $this->when($request->routeIs('*.statistics'), function () {
                return $this->getStatistics();
            }),
        ];
    }
} 