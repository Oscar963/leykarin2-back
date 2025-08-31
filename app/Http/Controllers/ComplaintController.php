<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    use LogsActivity;

    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * Listar todos las denuncias.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $complaints = $this->complaintService->getAllComplaintsByQuery($query, $perPage);

        return ComplaintResource::collection($complaints)->response();
    }

    /**
     * Guardar una nueva denuncia.
     * @param ComplaintRequest $request
     * @return JsonResponse
     */
    public function store(ComplaintRequest $request): JsonResponse
    {
        $complaint = $this->complaintService->createComplaint($request->validated());
        $this->logActivity('create_complaint', 'Usuario creó una denuncia con ID: ' . $complaint->id);
        
        $metadata = [
            'folio' => $complaint->folio,
            'created_at' => $complaint->created_at
        ];  

        return response()->json([
            'message' => 'Denuncia guardada exitosamente',
            'data' => new ComplaintResource($complaint),
            'meta' => $metadata
        ], 201);
    }

    /**
     * Mostrar una denuncia.
     * @param Complaint $complaint
     * @return JsonResponse
     */
    public function show(Complaint $complaint): JsonResponse
    {
        $this->logActivity('show_complaint', 'Usuario mostró una denuncia con ID: ' . $complaint->id);
        return response()->json([
            'data' => new ComplaintResource($complaint)
        ], 200);
    }

    /**
     * Actualizar una denuncia.
     * @param Complaint $complaint
     * @param ComplaintRequest $request
     * @return JsonResponse
     */
    public function update(Complaint $complaint, ComplaintRequest $request): JsonResponse
    {
        $updatedComplaint = $this->complaintService->updateComplaint($complaint, $request->validated());
        $this->logActivity('update_complaint', 'Usuario actualizó una denuncia con ID: ' . $updatedComplaint->id);
        return response()->json([
            'message' => 'Denuncia actualizada exitosamente',
            'data' => new ComplaintResource($updatedComplaint)
        ], 200);
    }

    /**
     * Eliminar una denuncia.
     * @param Complaint $complaint
     * @return JsonResponse
     */
    public function destroy(Complaint $complaint): JsonResponse
    {
        $this->complaintService->deleteComplaint($complaint);    
        $this->logActivity('delete_complaint', 'Usuario eliminó una denuncia con ID: ' . $complaint->id);
        return response()->json([
            'message' => 'Denuncia eliminada exitosamente'
        ], 200);
    }
}
