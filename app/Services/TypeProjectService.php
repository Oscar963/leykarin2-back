<?php

namespace App\Services;

use App\Models\TypeProject;

class TypeProjectService
{
    /**
     * Obtiene todos los tipos de proyecto
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTypeProjects()
    {
        return TypeProject::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene tipos de proyecto paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllTypeProjectsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = TypeProject::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un tipo de proyecto por su ID
     *
     * @param int $id ID del tipo de proyecto
     * @return TypeProject
     */
    public function getTypeProjectById($id)
    {
        return TypeProject::findOrFail($id);
    }

    /**
     * Crea un nuevo tipo de proyecto
     *
     * @param array $data Datos del tipo de proyecto
     * @return TypeProject
     */
    public function createTypeProject(array $data)
    {
        $typeProject = new TypeProject();
        $typeProject->name = trim($data['name']);
        $typeProject->description = trim($data['description']);
        $typeProject->save();

        return $typeProject;
    }

    /**
     * Actualiza un tipo de proyecto existente
     *
     * @param int $id ID del tipo de proyecto
     * @param array $data Datos actualizados
     * @return TypeProject
     */
    public function updateTypeProject($id, array $data)
    {
        $typeProject = $this->getTypeProjectById($id);
        $typeProject->name = trim($data['name']);
        $typeProject->description = trim($data['description']);
        $typeProject->save();

        return $typeProject;
    }

    /**
     * Elimina un tipo de proyecto
     *
     * @param int $id ID del tipo de proyecto
     * @return void
     */
    public function deleteTypeProject($id)
    {
        $typeProject = $this->getTypeProjectById($id);
        $typeProject->delete();
    }
} 