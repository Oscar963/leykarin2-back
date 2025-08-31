<?php

namespace App\Http\Controllers;

use App\Http\Requests\DenouncedRequest;
use App\Http\Resources\DenouncedResource;
use App\Models\Denounced;
use App\Services\DenouncedService;  
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DenouncedController extends Controller
{
    use LogsActivity;

    private DenouncedService $denouncedService;

    public function __construct(DenouncedService $denouncedService)
    {
        $this->denouncedService = $denouncedService;
    }

    /**
     * Listar todos los denunciantes.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $denounceds = $this->denouncedService->getAllDenouncedByQuery($query, $perPage);

        return DenouncedResource::collection($denounceds)->response();
    }

    /**
     * Guardar un nuevo denunciante .   
     * @param DenouncedRequest $request
     * @return JsonResponse
     */
    public function store(DenouncedRequest $request): JsonResponse
    {
        $denounced = $this->denouncedService->createDenounced($request->validated());
        $this->logActivity('create_denounced', 'Usuario creó un denunciante con ID: ' . $denounced->id);
        return response()->json([
            'message' => 'Denunciante guardado exitosamente',
            'data' => new DenouncedResource($denounced)
        ], 201);
    }

    /**
     * Mostrar un denunciante.
     * @param Denounced $denounced
     * @return JsonResponse
     */
    public function show(Denounced $denounced): JsonResponse
    {
        $this->logActivity('show_denounced', 'Usuario mostró un denunciante con ID: ' . $denounced->id);
        return response()->json([
            'data' => new DenouncedResource($denounced)
        ], 200);
    }

    /**
     * Actualizar un denunciante.
     * @param Denounced $denounced
     * @param DenouncedRequest $request
     * @return JsonResponse
     */
    public function update(Denounced $denounced, DenouncedRequest $request): JsonResponse
    {
        $updatedDenounced = $this->denouncedService->updateDenounced($denounced, $request->validated());
        $this->logActivity('update_denounced', 'Usuario actualizó el denunciante con ID: ' . $updatedDenounced->id);
        return response()->json([
            'message' => 'Denunciante actualizado exitosamente',
            'data' => new DenouncedResource($updatedDenounced)
        ], 200);
    }

    /**
     * Eliminar un denunciante.
     * @param Denounced $denounced
     * @return JsonResponse
     */
    public function destroy(Denounced $denounced): JsonResponse
    {
        $this->denouncedService->deleteDenounced($denounced);
        $this->logActivity('delete_denounced', 'Usuario eliminó el denunciante con ID: ' . $denounced->id);
        return response()->json([
            'message' => 'Denunciante eliminado exitosamente'
        ], 200);
    }
}
