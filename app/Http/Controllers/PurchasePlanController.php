<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchasePlanRequest;
use App\Http\Requests\UploadFileDecretoRequest;
use App\Http\Requests\UploadFormF1Request;
use App\Http\Resources\PurchasePlanResource;
use App\Services\PurchasePlanService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use App\Mail\PurchasePlanSent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchasePlanApproved;
use App\Mail\PurchasePlanRejected;

class PurchasePlanController extends Controller
{
    use LogsActivity;

    protected $purchasePlanService;

    public function __construct(PurchasePlanService $purchasePlanService)
    {
        $this->purchasePlanService = $purchasePlanService;
    }

    /**
     * Lista todos los planes de compra con paginación y filtrado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->purchasePlanService->getAllPurchasePlansByQuery($query, $perPage);

            return response()->json([
                'data' => PurchasePlanResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los planes de compra.'
            ], 500);
        }
    }

    /**
     * Lista todos los planes de compra con paginación y filtrado del año 
     */
    public function indexByYear(int $year, Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');

            $results = $this->purchasePlanService->getAllPurchasePlansByYear($year, $query, $perPage);

            return response()->json([
                'data' => PurchasePlanResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los planes de compra.'
            ], 500);
        }
    }

    /**
     * Lista todos los planes de compra con paginación y filtrado del año para un usuario específico
     */
    public function indexByYearForUser(int $year): JsonResponse
    {
        try {
            $idDirection = auth()->user()->directions->first()->id;

            $results = $this->purchasePlanService->getAllPurchasePlansByYearForUser($idDirection, $year);

            return response()->json([
                'data' => new PurchasePlanResource($results)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los planes de compra.'
            ], 500);
        }
    }

    /**
     * Muestra un plan de compra por su ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanById($id);

            return response()->json([
                'data' => new PurchasePlanResource($purchasePlan)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Plan de compra no encontrado.'
            ], 404);
        }
    }

    /**
     * Muestra un plan de compra por su token
     */
    public function showByToken(string $token): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanByToken($token);
            return response()->json([
                'data' => new PurchasePlanResource($purchasePlan)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Plan de compra no encontrado.'
            ], 404);
        }
    }

    /**
     * Muestra un plan de compra por año
     * Si no existe un plan para el año especificado, lo crea automáticamente
     */
    public function showByYear(int $year): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanByYear($year);

            // Si no existe un plan para el año especificado, lo creamos automáticamente
            if (!$purchasePlan) {
                $purchasePlan = $this->purchasePlanService->createDefaultPurchasePlan($year);
                $this->logActivity('create_purchase_plan', 'Se creó automáticamente un plan de compra para el año: ' . $year);
            }

            return response()->json([
                'data' => new PurchasePlanResource($purchasePlan)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métodos de Creación y Actualización
     */

    /**
     * Crea un nuevo plan de compra
     */
    public function store(PurchasePlanRequest $request): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->createPurchasePlan($request->validated());
            $this->logActivity('create_purchase_plan', 'Usuario creó un plan de compra con ID: ' . $purchasePlan->id);

            return response()->json([
                'message' => 'Plan de compra ha sido guardado exitosamente',
                'data' => new PurchasePlanResource($purchasePlan)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un plan de compra por su ID
     */
    public function update(int $id, PurchasePlanRequest $request): JsonResponse
    {
        try {
            $updated = $this->purchasePlanService->updatePurchasePlan($id, $request->validated());
            $this->logActivity('update_purchase_plan', 'Usuario actualizó el plan de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Plan de compra ha sido actualizado exitosamente',
                'data' => new PurchasePlanResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un plan de compra por su token
     */
    public function updateByToken(string $token, PurchasePlanRequest $request): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanByToken($token);
            $updated = $this->purchasePlanService->updatePurchasePlan($purchasePlan->id, $request->validated());
            $this->logActivity('update_purchase_plan', 'Usuario actualizó el plan de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Plan de compra ha sido actualizado exitosamente',
                'data' => new PurchasePlanResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métodos de Gestión de Archivos
     */

    /**
     * Sube un archivo de decreto
     */
    public function uploadDecreto(UploadFileDecretoRequest $request): JsonResponse
    {
        try {
            $upload = $this->purchasePlanService->uploadFileDecreto($request->validated());
            $this->logActivity('upload_file_decreto', 'Usuario subió un decreto con ID: ' . $upload->id);

            return response()->json([
                'message' => 'Decreto subido exitosamente',
                'data' => new PurchasePlanResource($upload)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al subir el decreto. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sube un archivo Form F1
     */
    public function uploadFormF1(UploadFormF1Request $request): JsonResponse
    {
        try {
            $upload = $this->purchasePlanService->uploadFileFormF1($request->validated());
            $this->logActivity('upload_file_form_f1', 'Usuario subió un Formulario F1 con ID: ' . $upload->id);

            return response()->json([
                'message' => 'Formulario F1 subido exitosamente',
                'data' => new PurchasePlanResource($upload)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al subir el Formulario F1. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métodos de Gestión de Estado
     */

    /**
     * Envía un plan de compra
     */
    public function send(string $token, Request $request): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanByToken($token);
            
            // Preparar datos adicionales para el estado
            $additionalData = [
                'sending_date' => $request->sending_date ?? now(),
                'plan_name' => $request->plan_name ?? $purchasePlan->name,
                'plan_year' => $request->plan_year ?? $purchasePlan->year,
                'total_amount' => $request->total_amount ?? $purchasePlan->getTotalAmount(),
                'available_budget' => $request->available_budget ?? $purchasePlan->getAvailableBudget(),
                'sending_comment' => $request->sending_comment ?? 'Plan de compras enviado para aprobación de la administración municipal'
            ];

            $this->purchasePlanService->sendPurchasePlan($purchasePlan, $request->status_id, $additionalData);

            $this->sendPurchasePlanReceiptEmail($purchasePlan);
            $this->logActivity('send_purchase_plan', 'Usuario envió el plan de compra con ID: ' . $purchasePlan->id);

            return response()->json([
                'message' => 'Plan de compra enviado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el estado de un plan de compra
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status_purchase_plan_id' => 'required',
                'sending_comment' => 'nullable|string',
            ]);

            $purchasePlan = $this->purchasePlanService->getPurchasePlanById($id);

            if ($validated['status_purchase_plan_id'] == 3) { // Aprobado
                $this->sendPurchasePlanApprovalEmail($purchasePlan);
            } else if ($validated['status_purchase_plan_id'] == 4) { // Rechazado
                $this->sendPurchasePlanRejectionEmail($purchasePlan);
            }

            $updated = $this->purchasePlanService->updatePurchasePlanStatus($id, $validated);
            $this->logActivity('update_purchase_plan_status', 'Usuario actualizó el estado del plan de compra con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Estado del plan de compra actualizado exitosamente',
                'data' => new PurchasePlanResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado del plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métodos de Eliminación
     */

    /**
     * Elimina un plan de compra
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->purchasePlanService->deletePurchasePlan($id);
            $this->logActivity('delete_purchase_plan', 'Usuario eliminó el plan de compra con ID: ' . $id);

            return response()->json([
                'message' => 'Plan de compra ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el plan de compra. ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Métodos de Notificación por Correo
     */

    /**
     * Envía el comprobante de envío del plan de compra por correo
     */
    protected function sendPurchasePlanReceiptEmail($purchasePlan)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanSent($purchasePlan));
        }
    }

    /**
     * Envía notificación de aprobación del plan de compra por correo
     */
    protected function sendPurchasePlanApprovalEmail($purchasePlan)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanApproved($purchasePlan));
        }
    }

    /**
     * Envía notificación de rechazo del plan de compra por correo
     */
    protected function sendPurchasePlanRejectionEmail($purchasePlan)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanRejected($purchasePlan));
        }
    }
}
