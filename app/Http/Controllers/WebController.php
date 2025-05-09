<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Services\WebService;
use Illuminate\Http\JsonResponse;
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

            return response()->json([
                'message' => 'Denuncia guardada exitosamente',
                'data' => new ComplaintResource($complaint)
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar la denuncia. ' . $e->getMessage()], 500);
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
