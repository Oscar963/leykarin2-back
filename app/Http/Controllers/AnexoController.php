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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Throwable;

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
            $query = $request->query('q');
            $perPage = $request->query('show');
            $banners = $this->anexoService->getAllAnexosByQuery($query, $perPage);

            return response()->json([
                'data' => AnexoResource::collection($banners)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los anexos.'
            ], 500);
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

            return response()->json([
                'message' => 'Anexo guardado exitosamente',
                'data' => new AnexoResource($anexo)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el anexo.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un anexo.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $anexo = $this->anexoService->getAnexoById($id);

            return response()->json([
                'data' => new AnexoResource($anexo)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Anexo no encontrado.'
            ], 404);
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

            return response()->json([
                'message' => 'Anexo actualizado exitosamente',
                'data' => new AnexoResource($updatedAnexo)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el anexo.' . $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Anexo eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el anexo.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar datos de anexos desde un archivo Excel.
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv,xls|max:10240',
            ]);

            Anexo::truncate();
            Excel::import(new AnexoImport, $request->file('file'));

            return response()->json([
                'message' => 'Datos importados correctamente.',
            ], 200);
        } catch (ValidationException $e) {
            // Errores de validación del request
            return response()->json([
                'message' => 'El archivo no es válido.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ExcelValidationException $e) {
            // Errores internos de validación del Excel
            return response()->json([
                'message' => 'El archivo contiene datos inválidos.',
                'errors' => $e->failures(),
            ], 422);
        } catch (Throwable $e) {
            // Registra el error completo para desarrolladores
            Log::error('Error al importar anexos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Devuelve un mensaje genérico al cliente
            return response()->json([
                'message' => 'Error al importar los datos. Verifique que el archivo tenga el formato correcto.',
            ], 500);
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
