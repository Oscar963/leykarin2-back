<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // 1. Empezamos con todos los atributos del modelo por defecto.
        $defaultData = parent::toArray($request); //Esto nos ahorra tener que listar todos los campos uno por uno.

        // 2. Definimos los campos que queremos formatear, añadir o sobrescribir.
        $customData = [
            'rut' => $this->formatRut($this->rut),

            // Sobrescribimos el formato de las fechas para asegurar el estándar ISO 8601.
            // Nota: Laravel por defecto ya convierte las fechas a este formato al serializar,
            // pero ser explícito aquí garantiza el comportamiento.
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('id')->toArray()),

            // --- EJEMPLO AVANZADO: Carga condicional de relaciones ---
            // Si en el futuro tienes una relación, por ejemplo, con 'Propietario':
            //
            // 'propietario' => new PropietarioResource($this->whenLoaded('propietario')),
            //
            // Esto es muy potente: la clave 'propietario' solo aparecerá en el JSON
            // si la cargaste explícitamente en el controlador (ej: Inmueble::with('propietario')->find(1)),
            // evitando problemas de N+1 queries.
        ];
        // 3. Fusionamos los datos por defecto con nuestros datos personalizados.
        return array_merge($defaultData, $customData); //Los campos en $customData tendrán prioridad.
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
