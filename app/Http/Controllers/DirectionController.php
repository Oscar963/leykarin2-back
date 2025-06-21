<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirectionRequest;
use App\Http\Resources\DirectionResource;
use App\Services\DirectionService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;

class DirectionController extends Controller
{
    use LogsActivity;

    protected $directionService;

    public function __construct(DirectionService $directionService)
    {
        $this->directionService = $directionService;
    }

    public function index(): JsonResponse
    {
        try {
            $directions = $this->directionService->getAllDirections();

            return response()->json([
                'data' => DirectionResource::collection($directions)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las direcciones.'
            ], 500);
        }
    }

    public function store(DirectionRequest $request): JsonResponse
    {
        try {
            $direction = $this->directionService->createDirection($request->validated());
            $this->logActivity('create_direction', 'Usuario creó una dirección con ID: ' . $direction->id);

            return response()->json([
                'message' => 'Dirección ha sido creada exitosamente',
                'data' => new DirectionResource($direction)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear la dirección. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $direction = $this->directionService->getDirectionById($id);

            return response()->json([
                'data' => new DirectionResource($direction)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Dirección no encontrada.'
            ], 404);
        }
    }

    public function update(int $id, DirectionRequest $request): JsonResponse
    {
        try {
            $direction = $this->directionService->updateDirection($id, $request->validated());
            $this->logActivity('update_direction', 'Usuario actualizó la dirección con ID: ' . $direction->id);

            return response()->json([
                'message' => 'Dirección ha sido actualizada exitosamente',
                'data' => new DirectionResource($direction)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la dirección. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->directionService->deleteDirection($id);
            $this->logActivity('delete_direction', 'Usuario eliminó la dirección con ID: ' . $id);

            return response()->json([
                'message' => 'Dirección ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la dirección. ' . $e->getMessage()
            ], 500);
        }
    }
}
