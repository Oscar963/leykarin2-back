<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ImportHistoriesService;
use App\Http\Resources\ImportHistoriesResource;
use App\Models\ImportHistories;
use App\Http\Requests\ImportHistoriesRequest;

class ImportHistoriesController extends Controller
{
    use LogsActivity;

    protected $importHistoriesService;

    public function __construct(ImportHistoriesService $importHistoriesService)
    {
        $this->importHistoriesService = $importHistoriesService;
    }

    /**
     * Listar todos los inmuebles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $filters = $request->only('rol_avaluo', 'foja');
        $importHistories = $this->importHistoriesService->getAllImportHistoriesByQuery($query, $perPage, $filters);

        $metadata = [
            'ultima_importacion' => '2025-07-23 15:30:00'
        ];

        return ImportHistoriesResource::collection($importHistories)->additional(['meta' => $metadata])->response();
    }

    /**
     * Guardar un nuevo inmueble.
     */
    public function store(ImportHistoriesRequest $request): JsonResponse
    {
        $importHistory = $this->importHistoriesService->createImportHistory($request->validated());

        $this->logActivity('create_import_history', 'Usuario creó un import_history con ID: ' . $importHistory->id);
        return response()->json([
            'message' => 'ImportHistory guardado exitosamente',
            'data' => new ImportHistoriesResource($importHistory)
        ], 201);
    }

    /**
     * Mostrar un import_history.
     */
    public function show(ImportHistories $importHistory): JsonResponse
    {
        $this->logActivity('show_import_history', 'Usuario mostró un import_history con ID: ' . $importHistory->id);
        return response()->json([
            'data' => new ImportHistoriesResource($importHistory)
        ], 200);
    }

    /**
     * Actualizar un import_history.
     */
    public function update(ImportHistories $importHistory, ImportHistoriesRequest $request): JsonResponse
    {
        $updatedImportHistory = $this->importHistoriesService->updateImportHistory($importHistory, $request->validated());

        $this->logActivity('update_import_history', 'Usuario actualizó el import_history con ID: ' . $updatedImportHistory->id);
        return response()->json([
            'message' => 'ImportHistory actualizado exitosamente',
            'data' => new ImportHistoriesResource($updatedImportHistory)
        ], 200);
    }

    /**
     * Eliminar un import_history.
     */
    public function destroy(ImportHistories $importHistory): JsonResponse
    {
        $this->importHistoriesService->deleteImportHistory($importHistory);

        $this->logActivity('delete_import_history', 'Usuario eliminó el import_history con ID: ' . $importHistory->id);
        return response()->json([
            'message' => 'ImportHistory eliminado exitosamente'
        ], 200);
    }
}
