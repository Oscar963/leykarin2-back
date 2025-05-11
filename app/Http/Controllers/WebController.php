<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Mail\ComplaintRegistered;
use App\Services\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class WebController extends Controller
{
    protected $webService;

    public function __construct(WebService $webService)
    {
        $this->webService = $webService;
    }

    public function storeComplaint(ComplaintRequest $request): JsonResponse
    {
        try {
            $data = $request->validated(); // Validar los datos del formulario

            // Preprocesar las evidencias desde el request
            $evidences = [];
            foreach ($request->input('evidences', []) as $index => $ev) {
                // Si existe un archivo, procesarlo
                $file = $request->file("evidences.$index.file");
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $evidences[] = [
                        'name' => $ev['name'] ?? 'documento', // Usa 'documento' si no se proporciona nombre
                        'file' => $file,
                    ];
                }
            }

            // Agregar las evidencias al array de datos que se pasa al servicio
            $data['evidences'] = $evidences;

            $complaint = $this->webService->createComplaint($data);

            // Enviar correo de confirmación
            $this->sendComplaintConfirmationEmail($complaint);

            return response()->json([
                'message' => 'Denuncia guardada exitosamente',
                'data' => new ComplaintResource($complaint)
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar la denuncia. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Envía el correo de confirmación al denunciante
     *
     * @param \App\Models\Complaint $complaint
     * @return void
     */
    private function sendComplaintConfirmationEmail($complaint): void
    {
        try {
            Mail::to($complaint->complainant->email)
                ->send(new ComplaintRegistered($complaint));
        } catch (Exception $e) {
            // Log del error pero no interrumpimos el flujo principal
            Log::error('Error al enviar correo de confirmación: ' . $e->getMessage());
        }
    }

    public function getAllTypeComplaint(): JsonResponse
    {
        try {
            $typeComplaints = $this->webService->getAllTypeComplaint();
            return response()->json(['data' => $typeComplaints], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los tipo de denuncia: ' . $e->getMessage()], 500);
        }
    }

    public function getAllDependence(): JsonResponse
    {
        try {
            $dependences = $this->webService->getAllDependence();
            return response()->json(['data' => $dependences], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las dependencias : ' . $e->getMessage()], 500);
        }
    }
}
