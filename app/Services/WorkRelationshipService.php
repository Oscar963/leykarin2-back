<?php

namespace App\Services;

use App\Models\WorkRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WorkRelationshipService
{
    /**
     * Obtiene todos los tipos de relacion laboral ordenados por fecha de creación (descendente).
     *
     * @return Collection<WorkRelationship>
     */
    public function getAllWorkRelationships(): Collection
    {
        return WorkRelationship::latest()->get();
    }

    /**
     * Obtiene todos los tipos de relacion laboral con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<WorkRelationship>
     */
    public function getAllWorkRelationshipsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return WorkRelationship::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo tipo de relacion laboral usando asignación masiva.
     *
     * @param array $data
     * @return WorkRelationship
     */
    public function createWorkRelationship(array $data): WorkRelationship
    {
        return WorkRelationship::create($data);
    }

    /**
     * Obtiene un tipo de relacion laboral por su ID.
     *
     * @param int $id
     * @return WorkRelationship
     */
    public function getWorkRelationshipById(int $id): WorkRelationship
    {
        return WorkRelationship::findOrFail($id);
    }

    /**
     * Actualiza un tipo de relacion laboral usando asignación masiva.
     *
     * @param WorkRelationship $workRelationship
     * @param array $data
     * @return WorkRelationship
     */
    public function updateWorkRelationship(WorkRelationship $workRelationship, array $data): WorkRelationship
    {
        $workRelationship->update($data);
        return $workRelationship;
    }

    /**
     * Elimina un tipo de relacion laboral.
     *
     * @param WorkRelationship $workRelationship
     * @return WorkRelationship
     */
    public function deleteWorkRelationship(WorkRelationship $workRelationship): WorkRelationship
    {
        $workRelationship->delete();
        return $workRelationship;
    }
}
