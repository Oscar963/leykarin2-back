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


class WebController extends Controller
{
    use LogsActivity;

    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService) {
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

        $this->logActivity('create_complaint', 'Usuario creó una denuncia con ID: ' . $complaint->id);
        return response()->json([
            'message' => 'Denuncia guardada exitosamente',
            'data' => new ComplaintResource($complaint) 
        ], 201);
    }
}
