<?php

namespace App\Services;

use App\Models\HierarchicalLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HierarchicalLevelService
{
    /**
     * Obtiene todos los niveles jerárquicos ordenados por fecha de creación (descendente).
     *
     * @return Collection<HierarchicalLevel>
     */
    public function getAllHierarchicalLevels(): Collection
    {
        return HierarchicalLevel::latest()->get();
    }

    /**
     * Obtiene todos los niveles jerárquicos con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<HierarchicalLevel>
     */
    public function getAllHierarchicalLevelsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return HierarchicalLevel::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo nivel jerárquico usando asignación masiva.
     *
     * @param array $data
     * @return HierarchicalLevel
     */
    public function createHierarchicalLevel(array $data): HierarchicalLevel
    {
        return HierarchicalLevel::create($data);
    }

    /**
     * Obtiene un nivel jerárquico por su ID.
     *
     * @param int $id
     * @return HierarchicalLevel
     */
    public function getHierarchicalLevelById(int $id): HierarchicalLevel
    {
        return HierarchicalLevel::findOrFail($id);
    }

    /**
     * Actualiza un nivel jerárquico usando asignación masiva.
     *
     * @param HierarchicalLevel $hierarchicalLevel
     * @param array $data
     * @return HierarchicalLevel
     */
    public function updateHierarchicalLevel(HierarchicalLevel $hierarchicalLevel, array $data): HierarchicalLevel
    {
        $hierarchicalLevel->update($data);
        return $hierarchicalLevel;
    }

    /**
     * Elimina un nivel jerárquico.
     *
     * @param HierarchicalLevel $hierarchicalLevel
     * @return HierarchicalLevel
     */
    public function deleteHierarchicalLevel(HierarchicalLevel $hierarchicalLevel): HierarchicalLevel
    {
        $hierarchicalLevel->delete();
        return $hierarchicalLevel;
    }
}
