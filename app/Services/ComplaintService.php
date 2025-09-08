<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Complainant;
use App\Models\Denounced;
use App\Models\Witness;
use App\Models\TypeDependency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    /**
     * Obtiene todos las denuncias ordenados por fecha de creación (descendente).
     *
     * @return Collection<Complaint>    
     */
    public function getAllComplaints(): Collection
    {
        return Complaint::latest()->get();
    }

    /**
     * Obtiene todos las denuncias con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<Complaint>
     */
    public function getAllComplaintsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return Complaint::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%"); 
                $q->orWhere('folio', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea una denuncia con sus relaciones y confirma archivos temporales
     *
     * @param array $data
     * @param string|null $sessionId
     * @return Complaint
     */
    public function createComplaint(array $data, ?string $sessionId = null): Complaint
    {
        return DB::transaction(function () use ($data, $sessionId) {
            // Crear el denunciante
            $complainant = Complainant::create([
                'type_dependency_id' => $data['complainant_dependence_id'],
                'name' => $data['complainant_name'],
                'address' => $data['complainant_address'],
                'rut' => $data['complainant_rut'],
                'phone' => $data['complainant_phone'],
                'charge' => $data['complainant_charge'],
                'email' => $data['complainant_email'],
                'unit' => $data['complainant_unit'],
                'function' => $data['complainant_function'],
                'grade' => $data['complainant_grade'],
                'birthdate' => $data['complainant_birthdate'],
                'entry_date' => $data['complainant_entry_date'],
                'contractual_status' => $data['complainant_contractual_status'],
                'is_victim' => $data['complainant_is_victim'],
            ]);

            // Crear el denunciado
            $denounced = Denounced::create([
                'name' => $data['denounced_name'],
                'address' => $data['denounced_address'],
                'rut' => $data['denounced_rut'],
                'phone' => $data['denounced_phone'],
                'charge' => $data['denounced_charge'],
                'email' => $data['denounced_email'],
                'unit' => $data['denounced_unit'],
                'function' => $data['denounced_function'],
                'grade' => $data['denounced_grade'],
            ]);

            // Generar código (correlativo) y token (único) automáticamente
            $code = $this->generateComplaintCode($complainant->type_dependency_id);
            $token = $this->generateComplaintToken();

            // Crear la denuncia principal
            $complaint = Complaint::create([
                'folio' => $code,
                'token' => $token,
                'type_complaint_id' => $data['type_complaint_id'],
                'complainant_id' => $complainant->id,
                'denounced_id' => $denounced->id,
                'hierarchical_level_id' => $data['hierarchical_level_id'],
                'work_relationship_id' => $data['work_relationship_id'],
                'supervisor_relationship_id' => $data['supervisor_relationship_id'],
                'circumstances_narrative' => $data['circumstances_narrative'],
                'consequences_narrative' => $data['consequences_narrative'],
            ]);

            // Crear testigos (si vienen en la data)
            if (!empty($data['witnesses']) && is_array($data['witnesses'])) {
                $witnessesPayload = [];
                foreach ($data['witnesses'] as $witness) {
                    if (!is_array($witness)) {
                        continue;
                    }
                    $witnessesPayload[] = [
                        'name' => $witness['name'] ?? null,
                        'phone' => $witness['phone'] ?? null,
                        'email' => $witness['email'] ?? null,
                    ];
                }
                if (!empty($witnessesPayload)) {
                    $complaint->witnesses()->createMany($witnessesPayload);
                }
            }

            // Confirmar archivos temporales si hay session_id
            if ($sessionId) {
                $fileService = app(FileService::class);
                $fileService->confirmTemporaryFiles($sessionId, $complaint);
            }

            return $complaint->load(['complainant', 'denounced', 'typeComplaint', 'hierarchicalLevel', 'workRelationship', 'supervisorRelationship', 'witnesses', 'files']);
        });
    }

    /**
     * Obtiene una denuncia por su ID.
     *
     * @param int $id
     * @return Complaint
     */
    public function getComplaintById(int $id): Complaint
    {
        return Complaint::findOrFail($id);
    }

    /**
     * Actualiza una denuncia.
     *
     * @param Complaint $complaint
     * @param array $data
     * @return Complaint
     */
    public function updateComplaint(Complaint $complaint, array $data): Complaint
    {
        $complaint->update($data);
        return $complaint;
    }

    /**
     * Elimina una denuncia.
     *
     * @param Complaint $complaint
     * @return Complaint
     */
    public function deleteComplaint(Complaint $complaint): Complaint
    {
        $complaint->delete();
        return $complaint;
    }

    /**
     * Genera un código correlativo para una nueva denuncia
     * Formato: PREFIX-####-YYYY
     */
    private function generateComplaintCode(int $typeDependencyId): string
    {
        $year = (int) now()->year;

        $typeDependency = TypeDependency::find($typeDependencyId);
        $prefix = $typeDependency ? $typeDependency->code : 'X';

        // Estricto: usar contador con lock transaccional
        // Nota: este método se ejecuta dentro de DB::transaction() desde createComplaint()
        $counter = DB::table('complaint_counters')
            ->where('type_dependency_id', $typeDependencyId)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (!$counter) {
            // Crear fila de contador para este año/dependencia
            DB::table('complaint_counters')->insert([
                'type_dependency_id' => $typeDependencyId,
                'year' => $year,
                'current_seq' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $nextSequence = 1;
        } else {
            $nextSequence = ((int) $counter->current_seq) + 1;
            DB::table('complaint_counters')
                ->where('id', $counter->id)
                ->update([
                    'current_seq' => $nextSequence,
                    'updated_at' => now(),
                ]);
        }

        return sprintf('%s-%04d-%d', strtoupper($prefix), $nextSequence, $year);
    }

    /**
     * Genera un token único de 12 caracteres alfanuméricos en mayúsculas
     */
    private function generateComplaintToken(): string
    {
        do {
            $token = Str::upper(Str::random(12));
        } while (Complaint::where('token', $token)->exists());

        return $token;
    }
}
