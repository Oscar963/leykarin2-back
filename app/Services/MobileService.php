<?php

namespace App\Services;

use App\Models\Mobile;

class MobileService
{
    /**
     * Obtener todos los registros de Mobile.
     */
    public function getAllMobiles()
    {
        return Mobile::orderBy('created_at', 'DESC')->get();
    }

    public function getAllMobilesByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Mobile::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('number', 'LIKE', "%{$query}%")
                    ->orWhere('office', 'LIKE', "%{$query}%")
                    ->orWhere('direction', 'LIKE', "%{$query}%")
                    ->orWhere('person', 'LIKE', "%{$query}%");
            });
        }
        return $queryBuilder->paginate($perPage);
    }

    /**
     * Crear un nuevo registro en Mobile.
     */
    public function createMobile(array $data)
    {
        $mobile = new Mobile();
        $mobile->number = $data['number'];
        $mobile->office = $data['office'] ?? null;
        $mobile->direction = $data['direction'] ?? null;
        $mobile->person = $data['person'] ?? null;
        $mobile->created_at = now();
        $mobile->save();

        return $mobile;
    }

    /**
     * Obtener un Mobile por ID.
     */
    public function getMobileById($id)
    {
        return Mobile::findOrFail($id);
    }

    /**
     * Actualizar un registro de Mobile.
     */
    public function updateMobile($id, array $data)
    {
        $mobile = $this->getMobileById($id);

        $mobile->number = $data['number'];
        $mobile->office = $data['office'] ?? null;
        $mobile->direction = $data['direction'] ?? null;
        $mobile->person = $data['person'] ?? null;
        $mobile->updated_at = now();
        $mobile->save();

        return $mobile;
    }

    /**
     * Eliminar un registro de Mobile.
     */
    public function deleteMobile($id)
    {
        $mobile = $this->getMobileById($id);
        $mobile->delete();
    }
}
