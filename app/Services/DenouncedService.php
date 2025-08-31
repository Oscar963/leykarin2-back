<?php

namespace App\Services;

use App\Models\Denounced;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DenouncedService
{
    /**
     * Obtiene todos los denunciantes ordenados por fecha de creación (descendente).
     *
     * @return Collection<Denounced>
     */
    public function getAllDenounced(): Collection
    {
        return Denounced::latest()->get();
    }

    /**
     * Obtiene todos los denunciantes con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<Denounced>
     */
    public function getAllDenouncedByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Denounced::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo denunciante usando asignación masiva.
     *
     * @param array $data
     * @return Denounced
     */
    public function createDenounced(array $data): Denounced
    {
        return Denounced::create($data);
    }

    /**
     * Obtiene un denunciante por su ID.
     *
     * @param int $id
     * @return Denounced
     */
    public function getDenouncedById(int $id): Denounced
    {
        return Denounced::findOrFail($id);
    }

    /**
     * Actualiza un denunciante usando asignación masiva.
     *
     * @param Denounced $denounced
     * @param array $data
     * @return Denounced
     */
    public function updateDenounced(Denounced $denounced, array $data): Denounced
    {
        $denounced->update($data);
        return $denounced;
    }

    /**
     * Elimina un denunciante.
     *
     * @param Denounced $denounced
     * @return Denounced
     */
    public function deleteDenounced(Denounced $denounced): Denounced
    {
        $denounced->delete();
        return $denounced;
    }
}
