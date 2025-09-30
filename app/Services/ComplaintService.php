<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Complainant;
use App\Models\ComplaintCounter;
use App\Models\Denounced;
use App\Models\TypeDependency;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    /**
     * Obtiene todos las denuncias con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<Complaint>
     */
    public function getAllComplaintsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        /** @var User|null $user */
        $user = auth()->user();

        // Mapear roles de Gestor por dependencia a su type_dependency_id
        $dependencyId = null;
        if ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('Gestor de Denuncias IMA')) {
                $dependencyId = 1; // IMA
            } elseif ($user->hasRole('Gestor de Denuncias DISAM')) {
                $dependencyId = 3; // DISAM
            } elseif ($user->hasRole('Gestor de Denuncias DEMUCE')) {
                $dependencyId = 2; // DEMUCE
            }
        }

        return Complaint::with(['complainant', 'complainant.typeDependency', 'files', 'witnesses'])
            ->latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where(function (Builder $subquery) use ($query) {
                    // Buscar por folio en la tabla complaints
                    $subquery->where('folio', 'LIKE', "%{$query}%")
                        // Buscar por nombre del denunciante en la tabla complainants
                        ->orWhereHas('complainant', function (Builder $complainantQuery) use ($query) {
                            $complainantQuery->where('name', 'LIKE', "%{$query}%");
                        });
                });
            })
            // Filtrado por dependencia según el rol del usuario autenticado
            ->when($dependencyId, function (Builder $q) use ($dependencyId) {
                $q->whereHas('complainant', function (Builder $subquery) use ($dependencyId) {
                    $subquery->where('type_dependency_id', $dependencyId);
                });
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

            $code = $this->generateComplaintCode($complainant->type_dependency_id);
            $token = $this->generateComplaintToken();

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

            if ($sessionId) {
                $fileService = app(FileService::class);
                $fileService->confirmTemporaryFiles($sessionId, $complaint);
            }

            return $complaint->load(['complainant', 'denounced', 'typeComplaint', 'hierarchicalLevel', 'workRelationship', 'supervisorRelationship', 'witnesses', 'files']);
        });
    }

    /**
     * Actualiza una denuncia existente.
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
     * @return bool
     */
    public function deleteComplaint(Complaint $complaint): bool
    {
        return $complaint->delete();
    }

    /**
     * Genera un código correlativo para una nueva denuncia
     */
    private function generateComplaintCode(int $typeDependencyId): string
    {
        $year = (int) now()->year;
        $typeDependency = TypeDependency::find($typeDependencyId);
        $prefix = $typeDependency ? $typeDependency->code : 'X';

        // Usar el modelo ComplaintCounter con lock optimista
        $counter = ComplaintCounter::getOrCreateCounter($typeDependencyId, $year);
        $nextSequence = $counter->incrementAndGet();

        return sprintf('%s-%04d-%d', strtoupper($prefix), $nextSequence, $year);
    }

    /**
     * Genera un token único, largo y criptográficamente seguro.
     */
    private function generateComplaintToken(): string
    {
        // Genera un string base64 seguro con 32 bytes de entropía.
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        // Verifica si el token ya existe para evitar colisiones.
        while (Complaint::where('token', $token)->exists()) {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        }

        return $token;
    }

    /**
     * Busca una denuncia por su token y carga las relaciones necesarias para el PDF.
     *
     * @param string $token
     * @return Complaint|null
     */
    public function getComplaintByTokenForDownload(string $token): ?Complaint
    {
        // El método original que tenías es exactamente lo que se necesita
        return Complaint::where('token', $token)
            ->with(Complaint::getStandardRelations())
            ->first();
    }

    /**
     * Genera el PDF de una denuncia a partir de la vista.
     */
    public function generateComplaintPdf(Complaint $complaint, array $extraData = [])
    {
        $data = array_merge([
            'complaint' => $complaint,
        ], $extraData);

        return Pdf::loadView('pdf.complaint', $data);
    }

    /**
     * Obtiene el nombre de archivo estándar para el PDF de una denuncia.
     */
    public function getComplaintPdfFilename($complaint): string
    {
        return 'folio-' . str_replace(['/', '-', ' '], '_', $complaint->folio) . '.pdf';
    }

    /**
     * Reenvía el comprobante de una denuncia por email.
     *
     * @param string $email
     * @param string $token
     * @return Complaint
     * @throws \Exception
     */
    public function reenviarComprobante(string $email, string $token): Complaint
    {
        // Buscar la denuncia por token
        $complaint = $this->getComplaintByTokenForDownload($token);
        
        if (!$complaint) {
            throw new \Exception('No se encontró una denuncia con el token proporcionado.');
        }

        // Validar que el email sea válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('El email proporcionado no es válido.');
        }

        // Enviar el email con el comprobante
        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\ComplaintEmail($complaint));
        } catch (\Exception $e) {
            throw new \Exception('Error al enviar el email: ' . $e->getMessage());
        }

        return $complaint;
    }
}
