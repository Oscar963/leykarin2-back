<?php

namespace App\Services;

use App\Models\TypeDependency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TypeDependencyService
{
    /**
     * Obtiene todos los tipos de dependencias ordenados por fecha de creación (descendente).
     *
     * @return Collection<TypeDependency>
     */
    public function getAllTypeDependencies(): Collection
    {
        return TypeDependency::latest()->get();
    }

    /**
     * Obtiene todos los tipos de dependencias con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<TypeDependency>
     */
    public function getAllTypeDependenciesByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return TypeDependency::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
                $q->orWhere('code', 'LIKE', "%{$query}%");
                $q->orWhere('email_notification', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo tipo de dependencia usando asignación masiva.
     *
     * @param array $data
     * @return TypeDependency
     */
    public function createTypeDependency(array $data): TypeDependency
    {
        return TypeDependency::create($data);
    }

    /**
     * Obtiene un tipo de dependencia por su ID.
     *
     * @param int $id
     * @return TypeDependency
     */
    public function getTypeDependencyById(int $id): TypeDependency
    {
        return TypeDependency::findOrFail($id);
    }

    /**
     * Actualiza un tipo de dependencia usando asignación masiva.
     *
     * @param TypeDependency $typeDependency
     * @param array $data
     * @return TypeDependency
     */
    public function updateTypeDependency(TypeDependency $typeDependency, array $data): TypeDependency
    {
        $typeDependency->update($data);
        return $typeDependency;
    }

    /**
     * Elimina un tipo de dependencia.
     *
     * @param TypeDependency $typeDependency
     * @return TypeDependency
     */
    public function deleteTypeDependency(TypeDependency $typeDependency): TypeDependency
    {
        $typeDependency->delete();
        return $typeDependency;
    }
}
