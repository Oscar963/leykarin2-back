<?php

namespace App\Services;

use App\Models\UnitPurchasing;

class UnitPurchasingService
{
    /**
     * Obtiene todas las unidades de compra
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUnitPurchasings()
    {
        return UnitPurchasing::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene unidades de compra paginadas con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUnitPurchasingsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = UnitPurchasing::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene una unidad de compra por su ID
     *
     * @param int $id ID de la unidad de compra
     * @return UnitPurchasing
     */
    public function getUnitPurchasingById($id)
    {
        return UnitPurchasing::findOrFail($id);
    }

    /**
     * Crea una nueva unidad de compra
     *
     * @param array $data Datos de la unidad de compra
     * @return UnitPurchasing
     */
    public function createUnitPurchasing(array $data)
    {
        $unitPurchasing = new UnitPurchasing();
        $unitPurchasing->name = trim($data['name']);
        $unitPurchasing->save();

        return $unitPurchasing;
    }

    /**
     * Actualiza una unidad de compra existente
     *
     * @param int $id ID de la unidad de compra
     * @param array $data Datos actualizados
     * @return UnitPurchasing
     */
    public function updateUnitPurchasing($id, array $data)
    {
        $unitPurchasing = $this->getUnitPurchasingById($id);
        $unitPurchasing->name = trim($data['name']);
        $unitPurchasing->save();

        return $unitPurchasing;
    }

    /**
     * Elimina una unidad de compra
     *
     * @param int $id ID de la unidad de compra
     * @return void
     */
    public function deleteUnitPurchasing($id)
    {
        $unitPurchasing = $this->getUnitPurchasingById($id);
        $unitPurchasing->delete();
    }
} 