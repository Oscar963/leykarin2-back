<?php

namespace App\Http\Controllers;

use App\Models\TypeComplaint;
use App\Models\TypeDependency;
use App\Models\ContractualStatus;
use App\Models\HierarchicalLevel;
use App\Models\SupervisorRelationship;
use App\Models\WorkRelationship;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ComplaintRequest;
use App\Services\ComplaintService;
use App\Traits\LogsActivity;
use App\Http\Resources\ComplaintResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ComplaintEmail;
use App\Models\Complaint;

class WebController extends Controller
{
    use LogsActivity;

    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * Obtiene datos necesarios para formularios web.
     *
     * @return JsonResponse
     */
    public function getFormData(): JsonResponse
    {
        return response()->json([
            'data' => [
                'type_complaints' => TypeComplaint::all(),
                'type_dependencies' => TypeDependency::all(),
                'contractual_statuses' => ContractualStatus::all(),
                'hierarchical_levels' => HierarchicalLevel::all(),
                'supervisor_relationships' => SupervisorRelationship::all(),
                'work_relationships' => WorkRelationship::all(),
            ]
        ]);
    }

    /**
     * Guardar una nueva denuncia con archivos temporales.
     * @param ComplaintRequest $request
     * @return JsonResponse
     */
    public function storeComplaint(ComplaintRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sessionId = $request->input('session_id');

        $complaint = $this->complaintService->createComplaint($data, $sessionId);

        $metadata = [
            'folio' => $complaint->folio,
            'created_at' => $complaint->created_at
        ];
        $this->logActivity('create_complaint', 'Usuario creó una denuncia con ID: ' . $complaint->id);
        $this->sendComplaintEmail($complaint);
        return response()->json([
            'message' => 'Denuncia registrada exitosamente',
            'data' => new ComplaintResource($complaint),
            'meta' => $metadata
        ], 201);
    }


    /**
     * Enviar comprobante de denuncia.
     * @param Complaint $complaint
     * @return void
     */
    public function sendComplaintEmail(Complaint $complaint): void
    {
        // Enviar comprobante al denunciante
        if ($complaint->complainant && !empty($complaint->complainant->email)) {
            Mail::to($complaint->complainant->email)->queue(new ComplaintEmail($complaint));
        }

        // Enviar notificación a la dependencia correspondiente
        if (
            $complaint->complainant &&
            $complaint->complainant->typeDependency &&
            !empty($complaint->complainant->typeDependency->email_notification)
        ) {

            Mail::to($complaint->complainant->typeDependency->email_notification)
                ->queue(new ComplaintEmail($complaint));
        }
    }
}
