<?php

namespace App\Services;

use App\Models\Direction;
use Illuminate\Support\Str;

class DirectionService
{
    /**
     * Obtiene todas las direcciones
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDirections()
    {
        return Direction::with(['director', 'users'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Obtiene direcciones paginadas con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllDirectionsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Direction::with(['director', 'users'])
            ->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('alias', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene una dirección por su ID
     *
     * @param int $id ID de la dirección
     * @return Direction
     */
    public function getDirectionById($id)
    {
        return Direction::with(['director', 'users'])->findOrFail($id);
    }

    /**
     * Crea una nueva dirección
     *
     * @param array $data Datos de la dirección
     * @return Direction
     */
    public function createDirection(array $data)
    {
        $direction = new Direction();
        $direction->name = trim($data['name']);
        $direction->alias = trim($data['alias']);
        $direction->director_id = $data['director_id'];
        $direction->save();

        return $direction->load(['director', 'users']);
    }

    /**
     * Actualiza una dirección existente
     *
     * @param int $id ID de la dirección
     * @param array $data Datos actualizados
     * @return Direction
     */
    public function updateDirection($id, array $data)
    {
        $direction = $this->getDirectionById($id);
        $direction->name = trim($data['name']);
        $direction->alias = trim($data['alias']);
        $direction->director_id = $data['director_id'];
        $direction->save();

        return $direction->load(['director', 'users']);
    }

    /**
     * Elimina una dirección
     *
     * @param int $id ID de la dirección
     * @return void
     */
    public function deleteDirection($id)
    {
        $direction = $this->getDirectionById($id);

        // Verificar si la dirección tiene usuarios asociados
        if ($direction->users()->count() > 0) {
            throw new \Exception('No se puede eliminar la dirección porque tiene usuarios asociados.');
        }

        // Verificar si la dirección tiene planes de compra asociados
        if ($direction->purchasePlans()->count() > 0) {
            throw new \Exception('No se puede eliminar la dirección porque tiene planes de compra asociados.');
        }

        $direction->delete();
    }
}
