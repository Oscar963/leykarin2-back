<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

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
            $user = auth()->user();
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');

            // Obtener el rol del usuario
            $userRole = $user->roles->first();
            if (!$userRole) {
                return response()->json(['message' => 'Usuario sin rol asignado'], 403);
            }

            // Mapear roles a dependencias
            $roleToDependence = [
                'Funcionario DISAM' => 'DISAM',
                'Funcionario IMA' => 'IMA',
                'Funcionario DEMUCE' => 'DEMUCE',
                'Administrador' => 'TODAS'
            ];

            // Si es un rol de funcionario específico, filtrar por dependencia
            $dependence = null;
            foreach ($roleToDependence as $role => $dep) {
                if ($userRole->name === $role) {
                    $dependence = $dep;
                    break;
                }
            }

            $complaints = $this->complaintService->getAllComplaintsByQuery($query, $perPage, $dependence);

            return response()->json([
                'data' => ComplaintResource::collection($complaints)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            Log::error('Error al obtener las denuncias', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error al obtener las denuncias.'], 500);
        }
    }

    /**
     * Generar pdf por denuncia.
     */
    public function generateComplaintPdf($token) 
    {
        $complaint = Complaint::with(['complainant', 'denounced', 'typeComplaint', 'witnesses'])
            ->where('token', $token)
            ->firstOrFail();

        $pdf = PDF::loadView('complaints.pdf', compact('complaint'));

        return $pdf->download("denuncia-{$complaint->folio}.pdf");
    }
}
