<?php

namespace App\Services;

use App\Models\TypePurchase;
use Illuminate\Support\Str;

class TypePurchaseService
{
    /**
     * Obtiene todos los tipos de compra
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTypePurchases()
    {
        return TypePurchase::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene tipos de compra paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllTypePurchasesByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = TypePurchase::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un tipo de compra por su ID
     *
     * @param int $id ID del tipo de compra
     * @return TypePurchase
     */
    public function getTypePurchaseById($id)
    {
        return TypePurchase::findOrFail($id);
    }

    /**
     * Crea un nuevo tipo de compra
     *
     * @param array $data Datos del tipo de compra
     * @return TypePurchase
     */
    public function createTypePurchase(array $data)
    {
        $typePurchase = new TypePurchase();
        $typePurchase->name = trim($data['name']);
        $typePurchase->key = $this->generateKey($data['name']);
        $typePurchase->save();

        return $typePurchase;
    }

    /**
     * Actualiza un tipo de compra existente
     *
     * @param int $id ID del tipo de compra
     * @param array $data Datos actualizados
     * @return TypePurchase
     */
    public function updateTypePurchase($id, array $data)
    {
        $typePurchase = $this->getTypePurchaseById($id);
        $typePurchase->name = trim($data['name']);
        $typePurchase->key = $this->generateKey($data['name']);
        $typePurchase->save();

        return $typePurchase;
    }

    /**
     * Elimina un tipo de compra
     *
     * @param int $id ID del tipo de compra
     * @return void
     */
    public function deleteTypePurchase($id)
    {
        $typePurchase = $this->getTypePurchaseById($id);
        $typePurchase->delete();
    }

    /**
     * Genera una clave única para el tipo de compra
     *
     * @param string $name Nombre del tipo de compra
     * @return string
     */
    private function generateKey(string $name): string
    {
        return Str::slug($name, '_');
    }
} 