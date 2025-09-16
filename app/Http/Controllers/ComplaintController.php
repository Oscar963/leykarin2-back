<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ComplaintController extends Controller
{
    use LogsActivity;

    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * Listar todas las denuncias
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
     * Crear una nueva denuncia.
     */
    public function store(ComplaintRequest $request): JsonResponse
    {
        try {
            $complaint = $this->complaintService->createComplaint($request->validated());

            $this->logActivity('create_complaint', 'Usuario creó una denuncia con ID: ' . $complaint->id);

            // Usamos etiquetas de caché para una invalidación más precisa
            Cache::tags(['complaints'])->flush();

            return response()->json([
                'message' => 'Denuncia creada exitosamente',
                'data' => new ComplaintResource($complaint),
            ], Response::HTTP_CREATED)
                ->header('Location', route('complaints.show', $complaint))
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error al crear la denuncia', $request->validated());
        }
    }

    /**
     * Mostrar una denuncia específica.
     */
    public function show(Complaint $complaint): JsonResponse
    {
        try {
            $complaint->load(Complaint::getStandardRelations());

            $this->logActivity('show_complaint', 'Usuario mostró una denuncia con ID: ' . $complaint->id);

            return response()->json([
                'data' => new ComplaintResource($complaint),
            ])
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('Cache-Control', 'private, max-age=300');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error al obtener la denuncia', ['complaint_id' => $complaint->id]);
        }
    }

    /**
     * Actualizar una denuncia existente.
     */
    public function update(Complaint $complaint, ComplaintRequest $request): JsonResponse
    {
        try {
            $updatedComplaint = $this->complaintService->updateComplaint($complaint, $request->validated());

            $this->logActivity('update_complaint', 'Usuario actualizó una denuncia con ID: ' . $updatedComplaint->id);

            Cache::tags(['complaints'])->flush();

            return response()->json([
                'message' => 'Denuncia actualizada exitosamente',
                'data' => new ComplaintResource($updatedComplaint),
            ])
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error al actualizar la denuncia', ['complaint_id' => $complaint->id, 'data' => $request->validated()]);
        }
    }

    /**
     * Eliminar una denuncia.
     */
    public function destroy(Complaint $complaint): JsonResponse
    {
        $this->complaintService->deleteComplaint($complaint);
        $this->logActivity('delete_complaint', 'Usuario eliminó la denuncia con ID: ' . $complaint->id);
        return response()->json([
            'message' => 'Denuncia eliminada exitosamente'
        ], 200);
    }

    /**
     * Descargar PDF de denuncia por token.
     */
    public function downloadPdf(string $token)
    {
        try {
            $complaint = $this->complaintService->getComplaintByTokenForDownload($token);
            if (!$complaint) {
                return response()->json([
                    'error' => 'Denuncia no encontrada',
                    'message' => 'No se encontró una denuncia con el token proporcionado'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->logActivity('download_complaint_pdf', 'Usuario descargó PDF de denuncia - Token: ' . $token . ', Folio: ' . $complaint->folio);

            $pdf = $this->complaintService->generateComplaintPdf($complaint, [
                'downloadedAt' => now(),
                'downloadedBy' => auth()->user()->name ?? 'Usuario',
                'userAgent' => request()->userAgent(),
                'ipAddress' => request()->ip()
            ]);

            $filename = $this->complaintService->getComplaintPdfFilename($complaint);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
                'Cache-Control' => 'no-cache, private',
                'X-Download-Message' => 'Denuncia ' . $complaint->folio . ' descargada correctamente',
                'X-Download-Status' => 'success'
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error al generar el PDF', ['token' => $token, 'ip' => request()->ip(), 'user_agent' => request()->userAgent()]);
        }
    }

    /**
     * Helper para manejar excepciones de forma consistente.
     */
    private function handleException(Throwable $e, string $userMessage, array $context = []): JsonResponse
    {
        Log::error($e->getMessage(), array_merge($context, [
            'user_id' => auth()->id(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]));

        $statusCode = $e instanceof ValidationException ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'error' => $userMessage,
            'message' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
        ], $statusCode);
    }
}
