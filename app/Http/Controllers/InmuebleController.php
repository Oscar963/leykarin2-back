<?php

namespace App\Http\Controllers;

use App\Http\Requests\InmuebleRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use App\Services\InmuebleService;
use App\Http\Resources\InmuebleResource;

class InmuebleController extends Controller
{
    use LogsActivity;

    protected $inmuebleService;

    public function __construct(InmuebleService $inmuebleService)
    {
        $this->inmuebleService = $inmuebleService;
    }

    /**
     * Listar todos los inmuebles.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('query');
            $perPage = $request->query('per_page');
            $filters = $request->only('rol_avaluo','foja');
            $inmuebles = $this->inmuebleService->getAllInmueblesByQuery($query, $perPage, $filters);

            return response()->json([
                'data' => InmuebleResource::collection($inmuebles)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los inmuebles.'
            ], 500);
        }
    }

    /**
     * Guardar un nuevo inmueble.
     */
    public function store(InmuebleRequest $request): JsonResponse
    {
        try {
            $inmueble = $this->inmuebleService->createInmueble($request->validated());
            $this->logActivity('create_inmueble', 'Usuario creó un inmueble con ID: ' . $inmueble->id);

            return response()->json([
                'message' => 'Inmueble guardado exitosamente',
                'data' => new InmuebleResource($inmueble)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el inmueble.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un inmueble.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inmueble = $this->inmuebleService->getInmuebleById($id);

            return response()->json([
                'data' => new InmuebleResource($inmueble)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Inmueble no encontrado.'
            ], 404);
        }
    }

    /**
     * Actualizar un inmueble.
     */
    public function update(int $id, InmuebleRequest $request): JsonResponse
    {
        try {
            $updatedInmueble = $this->inmuebleService->updateInmueble($id, $request->validated());
            $this->logActivity('update_inmueble', 'Usuario actualizó el inmueble con ID: ' . $updatedInmueble->id);

            return response()->json([
                'message' => 'Inmueble actualizado exitosamente',
                'data' => new InmuebleResource($updatedInmueble)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el inmueble.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un inmueble.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->inmuebleService->deleteInmueble($id);
            $this->logActivity('delete_inmueble', 'Usuario eliminó el inmueble con ID: ' . $id);

            return response()->json([
                'message' => 'Inmueble eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el inmueble.' . $e->getMessage()
            ], 500);
        }
    }
}
