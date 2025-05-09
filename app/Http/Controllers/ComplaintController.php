<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ComplaintController extends Controller
{
    use LogsActivity;

    protected $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * Listar todas las denuncias.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');
            $complaints = $this->complaintService->getAllComplaintsByQuery($query, $perPage);

            return response()->json([
                'data' => ComplaintResource::collection($complaints)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener las denuncias.'], 500);
        }
    }
}
