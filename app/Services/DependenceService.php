<?php

namespace App\Services;

use App\Models\Dependence;
use Illuminate\Support\Str;

class DependenceService
{
    public function getAllDependences()
    {
        return Dependence::orderBy('created_at', 'DESC')->get();
    }

    public function getAllDependencesByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Dependence::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    public function createDependence(array $data)
    {
        $dependence = new Dependence();
        $dependence->name = trim($data['name']);
        $dependence->key = Str::slug($data['name'], '_');

        $dependence->save();

        return $dependence;
    }

    public function getDependenceById($id)
    {
        return Dependence::findOrFail($id);
    }

    public function updateDependence($id, array $data)
    {
        $dependence = $this->getDependenceById($id);
        $dependence->name = trim($data['name']);
        $dependence->key = Str::slug($data['name'], '_');
        $dependence->save();

        return $dependence;
    }

    public function deleteDependence($id)
    {
        $dependence = $this->getDependenceById($id);
        $dependence->delete();
    }
} 