<?php

namespace App\Http\Controllers;

use App\Http\Requests\PopupRequest;
use App\Http\Resources\PopupResource;
use App\Models\Popup;
use Illuminate\Http\Request;
use App\Services\PopupService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;

class PopupController extends Controller
{
    use LogsActivity;

    protected $popupService;

    public function __construct(PopupService $popupService)
    {
        $this->popupService = $popupService;
    }

    /**
     * Listar todos los popups.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $show = $request->query('show');
            $popups = Popup::orderBy('created_at', 'DESC')->paginate($show);
            return response()->json(['data' =>  PopupResource::collection($popups)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los popups.'], 500);
        }
    }

    /**
     * Guardar un nuevo popup.
     */
    public function store(PopupRequest $request): JsonResponse
    {
        try {
            $popup = $this->popupService->createPopup($request->validated());
            $this->logActivity('create_popup', 'Usuario creó un popup con ID: ' . $popup->id);

            return response()->json(['message' => 'Popup guardado exitosamente', 'data' => new PopupResource($popup)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar el popup.' . $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $popup = $this->popupService->getPopupById($id);
            return response()->json(['data' => new PopupResource($popup)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Popup no encontrado.'], 404);
        }
    }
    /**
     * Actualizar un popup.
     */
    public function update(int $id, PopupRequest $request): JsonResponse
    {
        try {
            $updatedPopup = $this->popupService->updatePopup($id, $request->validated());
            $this->logActivity('update_popup', 'Usuario actualizó el popup con ID: ' . $updatedPopup->id);

            return response()->json(['message' => 'Popup actualizado exitosamente', 'data' => new PopupResource($updatedPopup)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el popup.' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un popup.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->popupService->deletePopup($id);
            $this->logActivity('delete_popup', 'Usuario eliminó el popup con ID: ' . $id);
            return response()->json(['message' => 'Popup eliminado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el popup.' . $e->getMessage()], 500);
        }
    }
}
