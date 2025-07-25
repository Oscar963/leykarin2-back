<?php

namespace App\Http\Controllers;

use App\Exports\InmueblesTemplateExport;
use App\Http\Requests\InmuebleRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InmuebleService;
use App\Http\Resources\InmuebleResource;
use App\Imports\InmueblesImport;
use App\Models\Inmueble;
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
            'ultima_importacion' => '2025-07-23 15:30:00'
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

        try {
            Excel::import(new InmueblesImport(), $request->file('file'), null, \Maatwebsite\Excel\Excel::XLSX, [
                'startRow' => 1,
            ]);
        } catch (ExcelValidationException | ValidationException $e) {
            throw $e;
        }

        DB::transaction(function () use ($request) {
            Inmueble::truncate();
            Excel::import(new InmueblesImport(), $request->file('file'));
        });

        $count = Inmueble::count();

        return response()->json([
            'message' => 'Se han importado ' . $count . ' inmuebles',
        ], 201);
    }

    /**
     * Descargar el template de importación.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new InmueblesTemplateExport(), 'inmuebles_template.xlsx');
    }
}
