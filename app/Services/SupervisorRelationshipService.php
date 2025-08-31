<?php

namespace App\Services;

use App\Models\SupervisorRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupervisorRelationshipService
{
    /**
     * Obtiene todos los tipos de relaciones ordenados por fecha de creación (descendente).
     *
     * @return Collection<SupervisorRelationship>
     */
    public function getAllSupervisorRelationships(): Collection
    {
        return SupervisorRelationship::latest()->get();
    }

    /**
     * Obtiene todos los tipos de relaciones con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<SupervisorRelationship>
     */
    public function getAllSupervisorRelationshipsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return SupervisorRelationship::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo tipo de relación usando asignación masiva.
     *
     * @param array $data
     * @return SupervisorRelationship
     */
    public function createSupervisorRelationship(array $data): SupervisorRelationship
    {
        return SupervisorRelationship::create($data);
    }

    /**
     * Obtiene un tipo de relación por su ID.
     *
     * @param int $id
     * @return SupervisorRelationship
     */
    public function getSupervisorRelationshipById(int $id): SupervisorRelationship
    {
        return SupervisorRelationship::findOrFail($id);
    }

    /**
     * Actualiza un tipo de relación usando asignación masiva.
     *
     * @param SupervisorRelationship $supervisorRelationship
     * @param array $data
     * @return SupervisorRelationship
     */
    public function updateSupervisorRelationship(SupervisorRelationship $supervisorRelationship, array $data): SupervisorRelationship
    {
        $supervisorRelationship->update($data);
        return $supervisorRelationship;
    }

    /**
     * Elimina un tipo de relación.
     *
     * @param SupervisorRelationship $supervisorRelationship
     * @return SupervisorRelationship
     */
    public function deleteSupervisorRelationship(SupervisorRelationship $supervisorRelationship): SupervisorRelationship
    {
        $supervisorRelationship->delete();
        return $supervisorRelationship;
    }
}
