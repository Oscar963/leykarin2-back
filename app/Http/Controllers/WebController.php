<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Mail\ComplaintRegistered;
use App\Mail\DependenceComplaintNotification;
use App\Services\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

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

            // Enviar comprobante al denunciante
            $this->sendComplaintReceipt($complaint);

            // Enviar notificación a la dependencia
            $this->sendDependenceNotification($complaint);

            return response()->json([
                'message' => 'Denuncia guardada exitosamente',
                'data' => new ComplaintResource($complaint)
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar la denuncia. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Envía el comprobante de denuncia al denunciante
     * 
     * @param \App\Models\Complaint $complaint
     * @return void
     */
    private function sendComplaintReceipt($complaint): void
    {
        try {
            // Cargar las relaciones necesarias para el correo
            $complaint->load(['complainant.dependence', 'denounced', 'typeComplaint', 'witnesses']);

            // Generar el PDF
            $pdf = PDF::loadView('complaints.pdf', compact('complaint'));

            // Enviar el correo con el PDF adjunto
            Mail::to($complaint->complainant->email)
                ->send(new ComplaintRegistered($complaint, $pdf, "denuncia-{$complaint->folio}.pdf"));

        } catch (Exception $e) {
            Log::error('Error al enviar comprobante de denuncia', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Envía la notificación a la dependencia correspondiente
     * 
     * @param \App\Models\Complaint $complaint
     * @return void
     */
    private function sendDependenceNotification($complaint): void
    {
        try {
            // Cargar las relaciones necesarias para el correo
            $complaint->load(['complainant.dependence', 'denounced', 'typeComplaint', 'witnesses']);

            $dependenceEmail = $this->getDependenceEmail($complaint->complainant->dependence->name);

            if ($dependenceEmail) {
                Mail::to($dependenceEmail)
                    ->send(new DependenceComplaintNotification(
                        $complaint,
                        $complaint->complainant->dependence->name
                    ));
            }
        } catch (Exception $e) {
            Log::error('Error al enviar notificación a la dependencia', [
                'complaint_id' => $complaint->id,
                'dependence' => $complaint->complainant->dependence->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Obtiene el correo electrónico según la dependencia
     * 
     * @param string $dependenceName
     * @return string|null
     */
    private function getDependenceEmail(string $dependenceName): ?string
    {
        switch (strtoupper($dependenceName)) {
            case 'IMA':
                return 'oscar.apata@municipalidadarica.cl';
                // return 'leykarin.ima@municipalidadarica.cl';
            case 'DISAM':
                return 'oscar.apata@municipalidadarica.cl';
                // return 'leykarin.disam@municipalidadarica.cl';
            case 'DEMUCE':
                return 'oscar.apata@municipalidadarica.cl';
                // return 'leykarin.demuce@municipalidadarica.cl';
            default:
                return null;
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
