<?php

namespace App\Services;

use App\Models\Inmueble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class InmuebleService
{
    /**
     * Obtiene todos los inmuebles ordenados por fecha de creación (descendente).
     * @return Collection 
     */
    public function getAllInmuebles()
    {
        return Inmueble::latest()->get();
    }

    /**
     * Obtiene todos los inmuebles con filtros y paginación.
     * @param string|null $query
     * @param int|null $perPage
     * @param array|null $filters
     * @return LengthAwarePaginator 
     */
    public function getAllInmueblesByQuery(?string $query, ?int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        return Inmueble::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('numero', 'LIKE', "%{$query}%")
                    ->orWhere('descripcion', 'LIKE', "%{$query}%")
                    ->orWhere('calle', 'LIKE', "%{$query}%")
                    ->orWhere('poblacion_villa', 'LIKE', "%{$query}%");
            })
            ->when(!empty($filters), function (Builder $q) use ($filters) {
                $q->where($filters);
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo inmueble usando asignación masiva.
     * @param array $data
     * @return Inmueble 
     */
    public function createInmueble(array $data): Inmueble
    {
        return Inmueble::create($data);
    }

    /**
     * Obtiene un inmueble por su ID.
     * @param int $id
     * @return Inmueble 
     */
    public function getInmuebleById(int $id): Inmueble
    {
        return Inmueble::findOrFail($id);
    }

    /**
     * Actualiza un inmueble usando asignación masiva.
     * @param Inmueble $inmueble
     * @param array $data
     * @return Inmueble 
     */
    public function updateInmueble(Inmueble $inmueble, array $data): Inmueble
    {
        $inmueble->update($data);
        return $inmueble;
    }

    /** 
     * Elimina un inmueble.
     * @param Inmueble $inmueble
     * @return Inmueble 
     */
    public function deleteInmueble(Inmueble $inmueble): Inmueble
    {
        $inmueble->delete();
        return $inmueble;
    }
}
