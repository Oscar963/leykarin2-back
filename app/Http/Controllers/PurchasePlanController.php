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
use App\Mail\PurchasePlanApproved;
use App\Mail\PurchasePlanRejected;
use App\Mail\PurchasePlanVisado;
use App\Mail\PurchasePlanAprobadoParaDecretar;
use App\Mail\PurchasePlanDecretado;
use App\Mail\PurchasePlanPublicado;
use Illuminate\Support\Facades\Mail;
use App\Models\Direction;

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
        // Verificar permiso para ver planes de compra
        if (!auth()->user()->can('view purchase plans')) {
            return response()->json(['message' => 'No tienes permisos para ver planes de compra'], 403);
        }

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
     * 
     * Para usuarios jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura):
     * - Carga automáticamente los datos de su dirección asignada
     * 
     * Para administradores (Administrador Municipal, Administrador del Sistema):
     * - Requiere un parámetro direction_id para especificar qué dirección cargar
     */
    public function showByYear(int $year, Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $directionId = null;

            // Verificar si el usuario es administrador (puede tener múltiples direcciones)
            if ($user->hasAnyRole(['Administrador del Sistema', 'Administrador Municipal'])) {
                // Los administradores deben especificar la dirección
                $directionId = $request->input('direction_id');
                
                if (!$directionId) {
                    return response()->json([
                        'message' => 'Los administradores deben especificar el parámetro direction_id para cargar los datos de una dirección específica.',
                        'required_parameter' => 'direction_id',
                        'user_roles' => $user->getRoleNames()->toArray()
                    ], 400);
                }

                // Verificar que la dirección existe
                $direction = Direction::find($directionId);
                if (!$direction) {
                    return response()->json([
                        'message' => 'La dirección especificada no existe.',
                        'direction_id' => $directionId
                    ], 404);
                }

            } else {
                // Para usuarios jerárquicos, obtener su dirección asignada
                $userDirection = $user->getMainDirection();
                
                if (!$userDirection) {
                    return response()->json([
                        'message' => 'No tienes una dirección asignada. Contacta al administrador del sistema.',
                        'user_roles' => $user->getRoleNames()->toArray()
                    ], 403);
                }

                $directionId = $userDirection->id;
            }

            // Obtener el plan de compra para el año y dirección específicos
            $purchasePlan = $this->purchasePlanService->getAllPurchasePlansByYearForUser($directionId, $year);

            // Si no existe un plan para el año y dirección especificados, lo creamos automáticamente
            if (!$purchasePlan) {
                try {
                    $purchasePlan = $this->purchasePlanService->createDefaultPurchasePlan($year, $directionId);
                    $this->logActivity('create_purchase_plan', "Se creó automáticamente un plan de compra para el año {$year} y dirección ID: {$directionId}");
                } catch (\Exception $e) {
                    // Manejar específicamente errores de validación de planes duplicados
                    if (str_contains($e->getMessage(), 'Ya existe un plan de compras')) {
                        return response()->json([
                            'message' => $e->getMessage(),
                            'error_type' => 'duplicate_plan'
                        ], 409); // Conflict - indica que ya existe un recurso
                    }
                    throw $e; // Re-lanzar otros errores
                }
            }

            return response()->json([
                'data' => new PurchasePlanResource($purchasePlan),
                'direction_info' => [
                    'id' => $directionId,
                    'name' => $purchasePlan->direction->name,
                    'alias' => $purchasePlan->direction->alias
                ],
                'user_info' => [
                    'roles' => $user->getRoleNames()->toArray(),
                    'is_admin' => $user->hasAnyRole(['Administrador del Sistema', 'Administrador Municipal'])
                ]
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
        } catch (\Exception $e) {
            // Manejar específicamente errores de validación de planes duplicados
            if (str_contains($e->getMessage(), 'Ya existe un plan de compras')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'duplicate_plan'
                ], 409); // Conflict - indica que ya existe un recurso
            }

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
        } catch (\Exception $e) {
            // Manejar específicamente errores de validación de planes duplicados
            if (str_contains($e->getMessage(), 'Ya existe otro plan de compras')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'duplicate_plan'
                ], 409); // Conflict - indica que ya existe un recurso
            }

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
            $this->purchasePlanService->sendPurchasePlan($purchasePlan, $request->status_id, $request->all());

            // Enviar email según el estado
            $comment = $request->input('sending_comment');
            switch ($request->status_id) {
                case 2: // Para aprobación
                    $this->sendPurchasePlanReceiptEmail($purchasePlan);
                    break;
                case 3: // Visado
                    $this->sendPurchasePlanVisadoEmail($purchasePlan, $comment);
                    break;
                case 4: // Aprobado para decretar
                    $this->sendPurchasePlanAprobadoParaDecretarEmail($purchasePlan, $comment);
                    break;
                case 5: // Rechazado
                    $this->sendPurchasePlanRejectionEmail($purchasePlan, $comment);
                    break;
                case 6: // Decretado
                    $this->sendPurchasePlanDecretadoEmail($purchasePlan, $comment);
                    break;
                case 7: // Publicado
                    $this->sendPurchasePlanPublicadoEmail($purchasePlan, $comment);
                    break;
                default:
                    // Para otros estados no se envía email
                    break;
            }

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

            // Enviar email según el estado
            $comment = $validated['sending_comment'] ?? null;
            switch ($validated['status_purchase_plan_id']) {
                case 2: // Para aprobación
                    $this->sendPurchasePlanReceiptEmail($purchasePlan);
                    break;
                case 3: // Visado
                    $this->sendPurchasePlanVisadoEmail($purchasePlan, $comment);
                    break;
                case 4: // Aprobado para decretar
                    $this->sendPurchasePlanAprobadoParaDecretarEmail($purchasePlan, $comment);
                    break;
                case 5: // Rechazado
                    $this->sendPurchasePlanRejectionEmail($purchasePlan, $comment);
                    break;
                case 6: // Decretado
                    $this->sendPurchasePlanDecretadoEmail($purchasePlan, $comment);
                    break;
                case 7: // Publicado
                    $this->sendPurchasePlanPublicadoEmail($purchasePlan, $comment);
                    break;
                default:
                    // Para otros estados no se envía email
                    break;
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
    protected function sendPurchasePlanApprovalEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanApproved($purchasePlan, $comment));
        }
    }

    /**
     * Envía notificación de rechazo del plan de compra por correo
     */
    protected function sendPurchasePlanRejectionEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanRejected($purchasePlan, $comment));
        }
    }

    /**
     * Envía notificación de visado del plan de compra por correo
     */
    protected function sendPurchasePlanVisadoEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanVisado($purchasePlan, $comment));
        }
    }

    /**
     * Envía notificación de aprobado para decretar del plan de compra por correo
     */
    protected function sendPurchasePlanAprobadoParaDecretarEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanAprobadoParaDecretar($purchasePlan, $comment));
        }
    }

    /**
     * Envía notificación de decretado del plan de compra por correo
     */
    protected function sendPurchasePlanDecretadoEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanDecretado($purchasePlan, $comment));
        }
    }

    /**
     * Envía notificación de publicado del plan de compra por correo
     */
    protected function sendPurchasePlanPublicadoEmail($purchasePlan, $comment = null)
    {
        $user = auth()->user();
        if ($user && $user->email) {
            Mail::to('oscar.apata@municipalidadarica.cl')->send(new PurchasePlanPublicado($purchasePlan, $comment));
        }
    }

    /**
     * Obtiene las direcciones disponibles para administradores
     * Solo para usuarios con roles de administrador
     */
    public function getAvailableDirections(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Verificar si el usuario es administrador
            if (!$user->hasAnyRole(['Administrador del Sistema', 'Administrador Municipal'])) {
                return response()->json([
                    'message' => 'Solo los administradores pueden acceder a esta funcionalidad.',
                    'user_roles' => $user->getRoleNames()->toArray()
                ], 403);
            }

            // Obtener todas las direcciones
            $directions = Direction::select('id', 'name', 'alias')
                ->orderBy('name')
                ->get();

            return response()->json([
                'data' => $directions,
                'total' => $directions->count(),
                'user_info' => [
                    'roles' => $user->getRoleNames()->toArray(),
                    'is_admin' => true
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las direcciones disponibles. ' . $e->getMessage()
            ], 500);
        }
    }
}
