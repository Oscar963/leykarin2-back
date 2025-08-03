<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $defaultData = parent::toArray($request);
        $customData = [
            'rut' => $this->formatRut($this->rut),
            'roles' => $this->whenLoaded('roles', fn() => $this->getRoleNames()),
            'permissions' => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()->pluck('name')),
        ];
        return array_merge($defaultData, $customData);
    }

    /**
     * Formatea un RUT chileno como XXXXXXXX-X (sin puntos, con guion)
     *
     * @param string $rut
     * @return string
     */
    private function formatRut($rut)
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut);

        $rut = strtolower($rut);

        if (strlen($rut) < 2) {
            return $rut;
        }

        $cuerpo = substr($rut, 0, -1);
        $dv = substr($rut, -1);

        return $cuerpo . '-' . $dv;
    }
}
