<?php

namespace App\Services;

use App\Models\FormF1;

class FormF1Service
{
    /**
     * Obtiene todos los formularios F1
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFormF1s()
    {
        return FormF1::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene formularios F1 paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllFormF1sByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = FormF1::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un formulario F1 por su ID
     *
     * @param int $id ID del formulario F1
     * @return FormF1
     */
    public function getFormF1ById($id)
    {
        return FormF1::findOrFail($id);
    }

    /**
     * Crea un nuevo formulario F1
     *
     * @param array $data Datos del formulario F1
     * @return FormF1
     */
    public function createFormF1(array $data)
    {
        $formF1 = new FormF1();
        $formF1->name = trim($data['name']);
        $formF1->description = isset($data['description']) ? trim($data['description']) : null;
        $formF1->url = isset($data['url']) ? trim($data['url']) : null;
        $formF1->type = isset($data['type']) ? trim($data['type']) : null;
        $formF1->size = isset($data['size']) ? $data['size'] : null;
        $formF1->amount = isset($data['amount']) ? $data['amount'] : 0;
        $formF1->created_by = auth()->id();
        $formF1->save();

        return $formF1;
    }

    /**
     * Actualiza un formulario F1 existente
     *
     * @param int $id ID del formulario F1
     * @param array $data Datos actualizados
     * @return FormF1
     */
    public function updateFormF1($id, array $data)
    {
        $formF1 = $this->getFormF1ById($id);
        $formF1->name = trim($data['name']);
        $formF1->description = isset($data['description']) ? trim($data['description']) : $formF1->description;
        $formF1->url = isset($data['url']) ? trim($data['url']) : $formF1->url;
        $formF1->type = isset($data['type']) ? trim($data['type']) : $formF1->type;
        $formF1->size = isset($data['size']) ? $data['size'] : $formF1->size;
        $formF1->amount = isset($data['amount']) ? $data['amount'] : $formF1->amount;
        $formF1->updated_by = auth()->id();
        $formF1->save();

        return $formF1;
    }

    /**
     * Elimina un formulario F1
     *
     * @param int $id ID del formulario F1
     * @return void
     */
    public function deleteFormF1($id)
    {
        $formF1 = $this->getFormF1ById($id);
        $formF1->delete();
    }
} 