<?php

namespace App\Services;


use App\Models\Complainant;
use App\Models\Complaint;
use App\Models\Denounced;
use App\Models\Dependence;
use App\Models\Evidence;
use App\Models\TypeComplaint;
use App\Models\Witness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebService
{
    public function createComplaint(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Registrar denunciante
            $complainant = new Complainant();
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
            $complainant->type_ladder = $data['type_ladder_complainant'];
            $complainant->grade = $data['grade_complainant'];
            $complainant->is_victim = $data['is_victim_complainant'];
            $complainant->dependence_id = Dependence::where('key', $data['dependence_complainant'])->value('id');
            $complainant->save();

            // Registrar denunciado
            $denounced = new Denounced();
            $denounced->name = $data['name_denounced'];
            $denounced->rut = $data['rut_denounced'];
            $denounced->phone = $data['phone_denounced'];
            $denounced->address = $data['address_denounced'];
            $denounced->charge = $data['charge_denounced'];
            $denounced->grade = $data['grade_denounced'];
            $denounced->email = $data['email_denounced'];
            $denounced->unit = $data['unit_denounced'];
            $denounced->function = $data['function_denounced'];
            $denounced->save();

            // Registrar denuncia
            $complaint = new Complaint();
            $complaint->folio = $this->generateFolio($data['dependence_complainant']);
            $complaint->token = Str::random(32);

            $complaint->date = now();
            $complaint->hierarchical_level = $data['hierarchical_level'];
            $complaint->work_directly = $data['work_directly'];
            $complaint->immediate_leadership = $data['immediate_leadership'];
            $complaint->narration_facts = $data['narration_facts'];
            $complaint->narration_consequences = $data['narration_consequences'];

            $complaint->complainant_id = $complainant->id;
            $complaint->denounced_id = $denounced->id;
            $complaint->type_complaint_id = TypeComplaint::where('key', $data['type_complaint'])->value('id');

            // Firmar denuncia
            if (isset($data['signature']) && $data['signature'] instanceof \Illuminate\Http\UploadedFile) {
                $fileName = uniqid() . '.' . $data['signature']->getClientOriginalExtension();
                $filePath = $data['signature']->storeAs('signatures', $fileName, 'public');
                $complaint->signature = url('storage/' . $filePath);
            }

            $complaint->save();


            // Registrar evidencias
            if (!empty($data['evidences']) && is_array($data['evidences'])) {
                foreach ($data['evidences'] as $ev) {
                    $evidence = new Evidence();
                    $evidence->name = $ev['name'] ?? 'documento';
                    $evidence->size = $ev['file']->getSize();
                    $evidence->type = $ev['file']->getClientMimeType();

                    if (isset($ev['file']) && $ev['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = Str::slug($evidence->name) . '-' . uniqid() . '.' . $ev['file']->getClientOriginalExtension();
                        $filePath = $ev['file']->storeAs('evidence', $fileName, 'public');
                        $evidence->url = url('storage/' . $filePath);
                        $evidence->complaint_id = $complaint->id;
                        $evidence->save();
                    }
                }
            }

            // Registrar testigos
            if (!empty($data['witnesses']) && is_array($data['witnesses'])) {
                foreach ($data['witnesses'] as $wit) {
                    $witness = new Witness();
                    $witness->name = $wit['name'];
                    $witness->email = $wit['email'] ?? null;
                    $witness->phone = $wit['phone'] ?? null;
                    $witness->complaint_id = $complaint->id;
                    $witness->save();
                }
            }

            return $complaint;
        });
    }

    public function getAllTypeComplaint()
    {
        return TypeComplaint::orderBy('id', 'ASC')->get();
    }

    public function getAllDependence()
    {
        return Dependence::orderBy('id', 'ASC')->get();
    }

    public function generateFolio(string $dependenceKey): string
    {
        $initial = $this->getFolioInitial($dependenceKey);
        $year = now()->year;

        $lastFolio = Complaint::whereYear('created_at', $year)
            ->where('folio', 'like', "{$initial}-%-$year")
            ->whereHas('complainant.dependence', function($query) use ($dependenceKey) {
                $query->where('key', $dependenceKey);
            })
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastFolio) {
            $matches = [];
            if (preg_match('/^[A-Z]-(\d+)-\d{4}$/', $lastFolio->folio, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }
        }

        return sprintf("%s-%04d-%d", $initial, $nextNumber, $year);
    }

    private function getFolioInitial(string $dependenceKey): string
    {
        // Mapear dependencia a letra
        $map = [
            'disam' => 'D',
            'demuce' => 'C',
            'ima' => 'I',
        ];

        $key = strtolower($dependenceKey);

        return $map[$key] ?? strtoupper(substr($dependenceKey, 0, 1));
    }
}
