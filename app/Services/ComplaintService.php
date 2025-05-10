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

    public function getAllComplaintsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Complaint::with(['complainant', 'complainant.dependence','denounced', 'witnesses', 'evidences'])->orderBy('created_at', 'DESC');

        return $queryBuilder->paginate($perPage);
    }
}
