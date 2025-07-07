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
            'numeracion' => $this->numeracion,
            'lote_sitio' => $this->lote_sitio,
            'manzana' => $this->manzana,
            'poblacion_villa' => $this->poblacion_villa,
            'foja' => $this->foja,
            'inscripcion_numero' => $this->inscripcion_numero,
            'inscripcion_anio' => $this->inscripcion_anio,
            'rol_avaluo' => $this->rol_avaluo,
            'superficie' => $this->superficie,
            'deslinde_norte' => $this->deslinde_norte,
            'deslinde_sur' => $this->deslinde_sur,
            'deslinde_este' => $this->deslinde_este,
            'deslinde_oeste' => $this->deslinde_oeste,
            'decreto_incorporacion' => $this->decreto_incorporacion,
            'decreto_destinacion' => $this->decreto_destinacion,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,

            // Links for HATEOAS
            '_links' => [
                'self' => [
                    'href' => route('inmuebles.show', $this->id),
                    'method' => 'GET'
                ],
                'update' => [
                    'href' => route('inmuebles.update', $this->id),
                    'method' => 'PUT'
                ],
                'delete' => [
                    'href' => route('inmuebles.destroy', $this->id),
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
