<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnexoRequest;
use App\Http\Resources\AnexoResource;
use App\Models\Anexo;
use App\Services\AnexoService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AnexoImport;
use App\Exports\AnexoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnexoController extends Controller
{
    use LogsActivity;

    protected $anexoService;

    public function __construct(AnexoService $anexoService)
    {
        $this->anexoService = $anexoService;
    }

    /**
     * Listar todos los anexos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $show = $request->query('show');
            $anexos = Anexo::orderBy('created_at', 'DESC')->paginate($show);
            return response()->json(['data' =>  AnexoResource::collection($anexos)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los anexos.'], 500);
        }
    }

    /**
     * Guardar un nuevo anexo.
     */
    public function store(AnexoRequest $request): JsonResponse
    {
        try {
            $anexo = $this->anexoService->createAnexo($request->validated());
            $this->logActivity('create_anexo', 'Usuario creó un anexo con ID: ' . $anexo->id);

            return response()->json(['message' => 'Anexo guardado exitosamente', 'data' => new AnexoResource($anexo)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar el anexo.' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar un anexo.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $anexo = $this->anexoService->getAnexoById($id);
            return response()->json(['data' => new AnexoResource($anexo)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Anexo no encontrado.'], 404);
        }
    }

    /**
     * Actualizar un anexo.
     */
    public function update(int $id, AnexoRequest $request): JsonResponse
    {
        try {
            $updatedAnexo = $this->anexoService->updateAnexo($id, $request->validated());
            $this->logActivity('update_anexo', 'Usuario actualizó el anexo con ID: ' . $updatedAnexo->id);

            return response()->json(['message' => 'Anexo actualizado exitosamente', 'data' => new AnexoResource($updatedAnexo)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el anexo.' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un anexo.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->anexoService->deleteAnexo($id);
            $this->logActivity('delete_anexo', 'Usuario eliminó el anexo con ID: ' . $id);
            return response()->json(['message' => 'Anexo eliminado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el anexo.' . $e->getMessage()], 500);
        }
    }

    /**
     * Importar datos de anexos desde un archivo Excel.
     */
    public function import(Request $request): JsonResponse
    {
        try {
            // Validar que el archivo esté presente y sea de tipo Excel
            $request->validate([
                'file' => 'required|mimes:xlsx,csv,xls|max:10240', // max 10MB
            ]);

            // Eliminar todos los datos existentes en la tabla antes de importar
            Anexo::truncate();

            // Importar los datos del archivo
            Excel::import(new AnexoImport, $request->file('file'));

            return response()->json(['message' => 'Datos importados correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al importar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar los anexos en formato Excel.
     */
    public function export(): BinaryFileResponse
    {
        $this->logActivity('download_file', 'Usuario exporto el excel de anexos');
        return Excel::download(new AnexoExport, 'anexos.xlsx');
    }
}
