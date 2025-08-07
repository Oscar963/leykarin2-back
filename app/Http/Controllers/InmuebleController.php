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
use App\Exports\InmueblesExport;
use App\Models\Inmueble;
use App\Models\ImportHistories;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Illuminate\Support\Facades\Log;

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
     * @param Request $request
     * @return JsonResponse 
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
     * @param InmuebleRequest $request
     * @return JsonResponse 
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
     * @param Inmueble $inmueble
     * @return JsonResponse 
     */
    public function show(Inmueble $inmueble): JsonResponse
    {

        $this->logActivity('show_inmueble', 'Usuario mostró un inmueble con ID: ' . $inmueble->id);
        return response()->json([
            'data' => new InmuebleResource($inmueble)
        ], 200);
    }

    /**
     * Actualizar un inmueble.
     * @param Inmueble $inmueble
     * @param InmuebleRequest $request
     * @return JsonResponse 
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
     * @param Inmueble $inmueble
     * @return JsonResponse 
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
     * @param Request $request
     * @return JsonResponse 
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $userId = auth()->id();
            $model = 'Inmueble';

            Inmueble::truncate();
            $importer = new InmueblesImport($filename, $userId, $model);
            Excel::import($importer, $file);

            $this->logActivity('import_inmuebles', 'Usuario importó inmuebles');
            
            $success = $importer->successCount ?? 0;
            $errors = $importer->errorCount ?? 0;
            $errorLog = $importer->errorLog ?? [];
            $total = $success + $errors;
            
            return response()->json([
                'message' => "Importación finalizada. Total: $total, Exitosos: $success, Fallidos: $errors",
                'total_imported' => $success,
                'total_failed' => $errors,
                'error_details' => $errorLog
            ], ($errors > 0 ? 207 : 201));
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'error' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
            
        } catch (ExcelValidationException $e) {
            $failures = $e->failures();
            $errorDetails = [];
            
            foreach ($failures as $failure) {
                $errorDetails[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }
            
            return response()->json([
                'message' => 'Error de validación en el archivo Excel',
                'error' => 'El archivo contiene errores de formato o validación',
                'error_details' => $errorDetails
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error durante la importación de inmuebles', [
                'user_id' => auth()->id(),
                'filename' => $filename ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error inesperado durante la importación',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Exportar inmuebles a Excel.
     * @return BinaryFileResponse 
     */
    public function export(): BinaryFileResponse
    {
        $this->logActivity('export_inmuebles', 'Usuario exportó inmuebles');
        return Excel::download(new InmueblesExport(), 'inmuebles.xlsx');
    }

    /**
     * Descargar el template de importación.
     * @return BinaryFileResponse 
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $this->logActivity('download_template', 'Usuario descargó el template de importación');
        return Excel::download(new InmueblesTemplateExport(), 'inmuebles_template.xlsx');
    }

    /**
     * Obtiene la fecha de la última importación exitosa de inmuebles.      
     * @return string 
     */
    private function getUltimaImportacionInmuebles()
    {
        return ImportHistories::where('model', 'Inmueble')
            ->where('status', 'completed')
            ->orderByDesc('finished_at')
            ->value('finished_at');
    }
}
