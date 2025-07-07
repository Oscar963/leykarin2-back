<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InmuebleResource extends JsonResource
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
            'numero' => $this->numero,
            'descripcion' => $this->descripcion,
            'calle' => $this->calle,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            
            // Links for HATEOAS
            '_links' => [
                'self' => [
                    'href' => route('api.inmuebles.show', $this->id),
                    'method' => 'GET'
                ],
                'update' => [
                    'href' => route('api.inmuebles.update', $this->id),
                    'method' => 'PUT'
                ],
                'delete' => [
                    'href' => route('api.inmuebles.destroy', $this->id),
                    'method' => 'DELETE'
                ]
            ]
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'resource_type' => 'inmueble',
                'api_version' => 'v1',
                'timestamp' => now()->toISOString()
            ]
        ];
    }
} 