<?php

namespace App\Http\Controllers;

use App\Exports\InmueblesTemplateExport;
use App\Http\Requests\InmuebleRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InmuebleService;
use App\Services\ImportHistoriesService;
use App\Http\Resources\InmuebleResource;
use App\Imports\InmueblesImport;
use App\Models\Inmueble;
use App\Models\ImportHistories;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

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
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $filters = $request->only('rol_avaluo', 'foja');
        $inmuebles = $this->inmuebleService->getAllInmueblesByQuery($query, $perPage, $filters);

        $metadata = [
            'ultima_importacion' => $this->getUltimaImportacionInmuebles()
        ];

        return InmuebleResource::collection($inmuebles)->additional(['meta' => $metadata])->response();
    }

    /**
     * Guardar un nuevo inmueble.
     */
    public function store(InmuebleRequest $request): JsonResponse
    {
        $inmueble = $this->inmuebleService->createInmueble($request->validated());
        $this->logActivity('create_inmueble', 'Usuario creó un inmueble con ID: ' . $inmueble->id);

        return response()->json([
            'message' => 'Inmueble guardado exitosamente',
            'data' => new InmuebleResource($inmueble)
        ], 201);
    }

    /**
     * Mostrar un inmueble.
     */
    public function show(Inmueble $inmueble): JsonResponse
    {
        return response()->json([
            'data' => new InmuebleResource($inmueble)
        ], 200);
    }

    /**
     * Actualizar un inmueble.
     */
    public function update(Inmueble $inmueble, InmuebleRequest $request): JsonResponse
    {
        $updatedInmueble = $this->inmuebleService->updateInmueble($inmueble, $request->validated());
        $this->logActivity('update_inmueble', 'Usuario actualizó el inmueble con ID: ' . $updatedInmueble->id);

        return response()->json([
            'message' => 'Inmueble actualizado exitosamente',
            'data' => new InmuebleResource($updatedInmueble)
        ], 200);
    }

    /**
     * Eliminar un inmueble.
     */
    public function destroy(Inmueble $inmueble): JsonResponse
    {
        $this->inmuebleService->deleteInmueble($inmueble);
        $this->logActivity('delete_inmueble', 'Usuario eliminó el inmueble con ID: ' . $inmueble->id);

        return response()->json([
            'message' => 'Inmueble eliminado exitosamente'
        ], 200);
    }

    /**
     * Procesar la importación de un archivo de inmuebles.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $userId = auth()->id();
        $model = 'Inmueble';

        try {
            DB::transaction(function () use ($file, $filename, $userId, $model) {
                // Limpiar la tabla antes de importar
                Inmueble::truncate();

                // Crear el importador con los parámetros necesarios
                $importer = new InmueblesImport($filename, $userId, $model);

                // Ejecutar la importación
                Excel::import($importer, $file);
            });

            return response()->json([
                'message' => 'Se han importado ' . Inmueble::count() . ' inmuebles exitosamente',
                'total_imported' => Inmueble::count()
            ], 201);
        } catch (ExcelValidationException | ValidationException $e) {
            // Si hay un error de validación, marcar la importación como fallida
            $importHistoriesService = app(ImportHistoriesService::class);

            // Buscar el último registro de importación para este usuario
            $lastImport = ImportHistories::where('imported_by', $userId)
                ->where('filename', $filename)
                ->where('status', 'processing')
                ->latest()
                ->first();

            if ($lastImport) {
                $importHistoriesService->failImport($lastImport, $e->getMessage());
            }

            throw $e;
        } catch (\Exception $e) {
            // Para cualquier otro error
            $importHistoriesService = app(ImportHistoriesService::class);

            $lastImport = ImportHistories::where('imported_by', $userId)
                ->where('filename', $filename)
                ->where('status', 'processing')
                ->latest()
                ->first();

            if ($lastImport) {
                $importHistoriesService->failImport($lastImport, 'Error inesperado durante la importación: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Error durante la importación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar el template de importación.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new InmueblesTemplateExport(), 'inmuebles_template.xlsx');
    }

    /**
     * Obtiene la fecha de la última importación exitosa de inmuebles.
     */
    private function getUltimaImportacionInmuebles()
    {
        return ImportHistories::where('model', 'Inmueble')
            ->where('status', 'completed')
            ->orderByDesc('finished_at')
            ->value('finished_at');
    }
}
