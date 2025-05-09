<?php

namespace App\Http\Controllers;

use App\Http\Requests\DependenceRequest;
use App\Http\Resources\DependenceResource;
use App\Services\DependenceService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class DependenceController extends Controller
{
    use LogsActivity;

    protected $dependenceService;

    public function __construct(DependenceService $dependenceService)
    {
        $this->dependenceService = $dependenceService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->dependenceService->getAllDependencesByQuery($query, $perPage);

            return response()->json([
                'data' => DependenceResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las dependencias.'
            ], 500);
        }
    }

    public function store(DependenceRequest $request): JsonResponse
    {
        try {
            $dependence = $this->dependenceService->createDependence($request->validated());
            $this->logActivity('create_dependence', 'Usuario creó una dependencia con ID: ' . $dependence->id);

            return response()->json([
                'message' => 'La dependencia ha sido guardada exitosamente',
                'data' => new DependenceResource($dependence)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar la dependencia. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $dependence = $this->dependenceService->getDependenceById($id);

            return response()->json([
                'data' => new DependenceResource($dependence)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Dependencia no encontrada.'
            ], 404);
        }
    }

    public function update(int $id, DependenceRequest $request): JsonResponse
    {
        try {
            $updated = $this->dependenceService->updateDependence($id, $request->validated());
            $this->logActivity('update_dependence', 'Usuario actualizó la dependencia con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Dependencia ha sido actualizada exitosamente',
                'data' => new DependenceResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la dependencia. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->dependenceService->deleteDependence($id);
            $this->logActivity('delete_dependence', 'Usuario eliminó la dependencia con ID: ' . $id);

            return response()->json([
                'message' => 'La dependencia ha sido eliminada exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la dependencia. ' . $e->getMessage()
            ], 500);
        }
    }
}
