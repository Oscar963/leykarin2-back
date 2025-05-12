<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Complainant;
use App\Models\Denounced;
use App\Models\Witness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    public function getAllComplaints()
    {
        return Complaint::with(['complainant', 'complainant.dependence','denounced', 'witnesses', 'evidences'])->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene todas las denuncias con paginación y búsqueda opcional
     * 
     * @param string|null $query
     * @param int|null $perPage
     * @param string|null $dependence
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllComplaintsByQuery(?string $query = null, ?int $perPage = null, ?string $dependence = null)
    {
        $complaintsQuery = Complaint::with(['complainant.dependence', 'denounced', 'typeComplaint', 'witnesses'])
            ->orderBy('created_at', 'desc');

        // Filtrar por dependencia si se especifica y no es 'TODAS'
        if ($dependence && $dependence !== 'TODAS') {
            $complaintsQuery->whereHas('complainant.dependence', function ($query) use ($dependence) {
                $query->where('name', $dependence);
            });
        }

        // Aplicar búsqueda si hay query
        if ($query) {
            $complaintsQuery->where(function ($q) use ($query) {
                $q->where('folio', 'like', "%{$query}%")
                    ->orWhereHas('complainant', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('rut', 'like', "%{$query}%");
                    })
                    ->orWhereHas('denounced', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('rut', 'like', "%{$query}%");
                    });
            });
        }

        return $complaintsQuery->paginate($perPage ?? 10);
    }
}
