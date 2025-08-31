<?php

namespace App\Services;

use App\Models\TypeComplaint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TypeComplaintService
{
    /**
     * Obtiene todos los tipos de denuncias ordenados por fecha de creación (descendente).
     *
     * @return Collection<TypeComplaint>
     */
    public function getAllTypeComplaints(): Collection
    {
        return TypeComplaint::latest()->get();
    }

    /**
     * Obtiene todos los tipos de denuncias con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<TypeComplaint>
     */
    public function getAllTypeComplaintsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return TypeComplaint::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo tipo de denuncia usando asignación masiva.
     *
     * @param array $data
     * @return TypeComplaint
     */
    public function createTypeComplaint(array $data): TypeComplaint
    {
        return TypeComplaint::create($data);
    }

    /**
     * Obtiene un tipo de denuncia por su ID.
     *
     * @param int $id
     * @return TypeComplaint
     */
    public function getTypeComplaintById(int $id): TypeComplaint
    {
        return TypeComplaint::findOrFail($id);
    }

    /**
     * Actualiza un tipo de denuncia usando asignación masiva.
     *
     * @param TypeComplaint $typeComplaint
     * @param array $data
     * @return TypeComplaint
     */
    public function updateTypeComplaint(TypeComplaint $typeComplaint, array $data): TypeComplaint
    {
        $typeComplaint->update($data);
        return $typeComplaint;
    }

    /**
     * Elimina un tipo de denuncia.
     *
     * @param TypeComplaint $typeComplaint
     * @return TypeComplaint
     */
    public function deleteTypeComplaint(TypeComplaint $typeComplaint): TypeComplaint
    {
        $typeComplaint->delete();
        return $typeComplaint;
    }
}
