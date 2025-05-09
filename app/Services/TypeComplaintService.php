<?php

namespace App\Services;

use App\Models\TypeComplaint;
use Illuminate\Support\Str;

class TypeComplaintService
{
    public function getAllTypeComplaints()
    {
        return TypeComplaint::orderBy('created_at', 'DESC')->get();
    }

    public function getAllTypeComplaintsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = TypeComplaint::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    public function createTypeComplaint(array $data)
    {
        $typeComplaint = new TypeComplaint();
        $typeComplaint->name = trim($data['name']);
        $typeComplaint->key = Str::slug($data['name'], '_');

        $typeComplaint->save();

        return $typeComplaint;
    }

    public function getTypeComplaintById($id)
    {
        return TypeComplaint::findOrFail($id);
    }

    public function updateTypeComplaint($id, array $data)
    {
        $typeComplaint = $this->getTypeComplaintById($id);
        $typeComplaint->name = trim($data['name']);
        $typeComplaint->key = Str::slug($data['name'], '_');
        $typeComplaint->save();

        return $typeComplaint;
    }

    public function deleteTypeComplaint($id)
    {
        $typeComplaint = $this->getTypeComplaintById($id);
        $typeComplaint->delete();
    }
}
