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

    public function createComplaint(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Crear denunciante
            $complainant = new Complainant();
            $complainant->dependence = $data['dependence_complainant'];
            $complainant->name = $data['name_complainant'];
            $complainant->rut = $data['rut_complainant'];
            $complainant->phone = $data['phone_complainant'];
            $complainant->email = $data['email_complainant'];
            $complainant->address = $data['address_complainant'];
            $complainant->charge = $data['charge_complainant'];
            $complainant->unit = $data['unit_complainant'];
            $complainant->function = $data['function_complainant'];
            $complainant->grade_eur = $data['grade_eur_complainant'];
            $complainant->date_income = $data['date_income_complainant'];
            $complainant->type_contract = $data['type_contract_complainant'];
            $complainant->grade = $data['grade_complainant'];
            $complainant->type_ladder = $data['type_ladder_complainant'];
            $complainant->is_victim = $data['is_victim_complainant'];
            $complainant->save(); // guardar una sola vez

            // Crear denuncia
            $complaint = new Complaint();
            $complaint->type = $data['type_complaint'];
            $complaint->date = now();
            $complaint->complainant_id = $complainant->id;
            $complaint->save();

            return $complaint;
        });
    }

}
