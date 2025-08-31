<?php

namespace App\Services;

use App\Models\Complainant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ComplainantService
{
    /**
     * Obtiene todos los denunciantes ordenados por fecha de creación (descendente).
     *
     * @return Collection<Complainant>
     */
    public function getAllComplainants(): Collection
    {
        return Complainant::latest()->get();
    }

    /**
     * Obtiene todos los denunciantes con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<Complainant>
     */
    public function getAllComplainantsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Complainant::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo denunciante usando asignación masiva.
     *
     * @param array $data
     * @return Complainant
     */
    public function createComplainant(array $data): Complainant
    {
        return Complainant::create($data);
    }

    /**
     * Obtiene un denunciante por su ID.
     *
     * @param int $id
     * @return Complainant
     */
    public function getComplainantById(int $id): Complainant
    {
        return Complainant::findOrFail($id);
    }

    /**
     * Actualiza un denunciante usando asignación masiva.
     *
     * @param Complainant $complainant
     * @param array $data
     * @return Complainant
     */
    public function updateComplainant(Complainant $complainant, array $data): Complainant
    {
        $complainant->update($data);
        return $complainant;
    }

    /**
     * Elimina un denunciante.
     *
     * @param Complainant $complainant
     * @return Complainant
     */
    public function deleteComplainant(Complainant $complainant): Complainant
    {
        $complainant->delete();
        return $complainant;
    }
}
