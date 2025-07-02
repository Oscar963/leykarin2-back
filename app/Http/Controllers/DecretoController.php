<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecretoRequest;
use App\Http\Resources\DecretoResource;
use App\Services\DecretoService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DecretoController extends Controller
{
    use LogsActivity;

    protected $decretoService;

    public function __construct(DecretoService $decretoService)
    {
        $this->decretoService = $decretoService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->decretoService->getAllDecretosByQuery($query, $perPage);

            return response()->json([
                'data' => DecretoResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los decretos.'
            ], 500);
        }
    }

    public function store(DecretoRequest $request): JsonResponse
    {
        try {
            $decreto = $this->decretoService->createDecreto($request->validated());
            
            // ✅ ENVIAR NOTIFICACIÓN SI HAY PLAN DE COMPRA ASOCIADO
            if ($decreto->purchasePlan) {
                $this->sendDecretoCreatedNotification($decreto->purchasePlan);
            }
            
            $this->logActivity('create_decreto', 'Usuario creó un decreto con ID: ' . $decreto->id);

            return response()->json([
                'message' => 'Decreto ha sido guardado exitosamente y plan de compra marcado como Decretado',
                'data' => new DecretoResource($decreto)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el decreto. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envía notificación cuando se crea un decreto
     */
    private function sendDecretoCreatedNotification($purchasePlan)
    {
        // Enviar email de notificación
        \Illuminate\Support\Facades\Mail::to('oscar.apata@municipalidadarica.cl')
            ->send(new \App\Mail\PurchasePlanDecretado($purchasePlan, 'Estado cambiado automáticamente a Decretado al crear el decreto'));
    }

    public function show(int $id): JsonResponse
    {
        try {
            $decreto = $this->decretoService->getDecretoById($id);

            return response()->json([
                'data' => new DecretoResource($decreto)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Decreto no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, DecretoRequest $request): JsonResponse
    {
        try {
            $updated = $this->decretoService->updateDecreto($id, $request->validated());
            $this->logActivity('update_decreto', 'Usuario actualizó el decreto con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Decreto ha sido actualizado exitosamente',
                'data' => new DecretoResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el decreto. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            // Obtener el decreto antes de eliminarlo para enviar notificación
            $decreto = $this->decretoService->getDecretoById($id);
            $purchasePlan = $decreto->purchasePlan;
            
            $this->decretoService->deleteDecreto($id);
            
            // ✅ ENVIAR NOTIFICACIÓN SI HAY PLAN DE COMPRA ASOCIADO
            if ($purchasePlan) {
                $this->sendDecretoRemovedNotification($purchasePlan);
            }
            
            $this->logActivity('delete_decreto', 'Usuario eliminó el decreto con ID: ' . $id);

            return response()->json([
                'message' => 'Decreto ha sido eliminado exitosamente y plan de compra revertido a "Aprobado para decretar"'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el decreto. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envía notificación cuando se elimina un decreto
     */
    private function sendDecretoRemovedNotification($purchasePlan)
    {
        // Enviar email de notificación
        \Illuminate\Support\Facades\Mail::to('oscar.apata@municipalidadarica.cl')
            ->send(new \App\Mail\DecretoRemoved($purchasePlan));
    }

    /**
     * Descargar un archivo por ID.
     */
    public function download(int $id): BinaryFileResponse
    {
        return $this->decretoService->downloadFile($id);
    }
} 