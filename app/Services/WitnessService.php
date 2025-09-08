<?php

namespace App\Services;

use App\Models\Witness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WitnessService
{
    /**
     * Obtiene todos los testigos ordenados por fecha de creación (descendente).
     *
     * @return Collection<Witness>
     */
    public function getAllWitnesses(): Collection
    {
        return Witness::latest()->get();
    }

    /**
     * Obtiene todos los testigos con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<Witness>
     */
    public function getAllWitnessesByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Witness::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
                $q->orWhere('phone', 'LIKE', "%{$query}%");
                $q->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo testigo usando asignación masiva.
     *
     * @param array $data
     * @return Witness
     */
    public function createWitness(array $data): Witness
    {
        return Witness::create($data);
    }

    /**
     * Obtiene un testigo por su ID.
     *
     * @param int $id
     * @return Witness
     */
    public function getWitnessById(int $id): Witness
    {
        return Witness::findOrFail($id);
    }

    /**
     * Actualiza un testigo usando asignación masiva.
     *
     * @param Witness $witness
     * @param array $data
     * @return Witness
     */
    public function updateWitness(Witness $witness, array $data): Witness
    {
        $witness->update($data);
        return $witness;
    }

    /**
     * Elimina un testigo.
     *
     * @param Witness $witness
     * @return Witness
     */
    public function deleteWitness(Witness $witness): Witness
    {
        $witness->delete();
        return $witness;
    }
}
