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

class PurchasePlanController extends Controller
{
    use LogsActivity;

    protected $purchasePlanService;

    public function __construct(PurchasePlanService $purchasePlanService)
    {
        $this->purchasePlanService = $purchasePlanService;
    }
    // Listar planes de compra
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
     * Envía el comprobante de envío del plan de compra por correo al usuario autenticado.
     */
    protected function sendPurchasePlanReceiptEmail($purchasePlan)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanSent($purchasePlan));
            //Mail::to($user->email)->send(new PurchasePlanSent($purchasePlan));
        }
    }

    public function send(string $token, Request $request): JsonResponse
    {
        try {
            $purchasePlan = $this->purchasePlanService->getPurchasePlanByToken($token);
            $this->purchasePlanService->sendPurchasePlan($purchasePlan, $request->status_id);

            // Enviar comprobante por correo
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
}
