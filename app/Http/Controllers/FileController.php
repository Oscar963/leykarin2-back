<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Services\FileService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    use LogsActivity;

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Listar todos los archivos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $show = $request->query('show');
            $pages = File::orderBy('created_at', 'DESC')->paginate($show);
            return response()->json(['data' =>  FileResource::collection($pages)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener las páginas.'], 500);
        }
    }

    /**
     * Guardar un nuevo archivo.
     */
    public function store(FileRequest $request): JsonResponse
    {
        try {
            $file = $this->fileService->uploadFile($request->validated());
            $this->logActivity('upload_file', 'Usuario subió un archivo: ' . $file->name);

            return response()->json(['message' => 'Archivo subido exitosamente', 'data' => new FileResource($file)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al subir el archivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un archivo.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->fileService->deleteFile($id);
            $this->logActivity('delete_file', 'Usuario eliminó el archivo con ID: ' . $id);
            return response()->json(['message' => 'Archivo eliminado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el archivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descargar un archivo por ID.
     */
    public function download(int $id): BinaryFileResponse
    {
        try {
            return $this->fileService->downloadFile($id);
            $this->logActivity('download_file', 'Usuario descargo el archivo con ID: ' . $id);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al descargar el archivo: ' . $e->getMessage()], 500);
        }
    }
}
