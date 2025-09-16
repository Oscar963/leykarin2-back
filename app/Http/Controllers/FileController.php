<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\TemporaryFile;
use App\Services\FileService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class FileController extends Controller
{
    use LogsActivity;

    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Listar todos los archivos.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $files = $this->fileService->getAllFilesByQuery($query, $perPage);

        return FileResource::collection($files)->response();
    }

    /**
     * Guardar un nuevo archivo.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $file = $this->fileService->createFile($request->all());
        $this->logActivity('create_file', 'Usuario creó un archivo con ID: ' . $file->id);
        return response()->json([
            'message' => 'Archivo guardado exitosamente',
            'data' => new FileResource($file)
        ], 201);
    }

    /**
     * Mostrar un archivo.
     * @param File $file
     * @return JsonResponse
     */
    public function show(File $file): JsonResponse
    {
        $this->logActivity('show_file', 'Usuario mostró un archivo con ID: ' . $file->id);
        return response()->json([
            'data' => new FileResource($file)
        ], 200);
    }

    /**
     * Actualizar un archivo.
     * @param File $file
     * @param Request $request
     * @return JsonResponse
     */
    public function update(File $file, Request $request): JsonResponse
    {
        $updatedFile = $this->fileService->updateFile($file, $request->all());
        $this->logActivity('update_file', 'Usuario actualizó el archivo con ID: ' . $updatedFile->id);
        return response()->json([
            'message' => 'Archivo actualizado exitosamente',
            'data' => new FileResource($updatedFile)
        ], 200);
    }

    /**
     * Eliminar un archivo.
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);
        $this->logActivity('delete_file', 'Usuario eliminó el archivo con ID: ' . $file->id);
        return response()->json([
            'message' => 'Archivo eliminado exitosamente'
        ], 200);
    }

    /**
     * Subir archivos de evidencia para una entidad.
     * @param Request $request
     * @param string $modelType
     * @param int $modelId
     * @return JsonResponse
     */
    public function uploadEvidence(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => File::getValidationRules(File::TYPE_EVIDENCE),
            'base_folder' => 'sometimes|string',
            'replace_existing' => 'sometimes|boolean',
            'disk' => 'sometimes|string',
            'custom_names' => 'sometimes|array',
            'custom_names.*' => 'nullable|string',
        ]);

        try {
            $modelClass = 'App\\Models\\' . ucfirst($modelType);
            if (!class_exists($modelClass)) {
                return response()->json(['message' => 'Modelo no válido'], 400);
            }

            $model = $modelClass::findOrFail($modelId);

            $uploadedFiles = $this->fileService->uploadFiles(
                $model,
                $request->file('files'),
                File::TYPE_EVIDENCE,
                [
                    'disk' => $request->input('disk', 'public'),
                    'replaceExisting' => (bool) $request->input('replace_existing', false),
                    'customNames' => $request->input('custom_names', []),
                    'baseFolder' => $request->input('base_folder', ''),
                ]
            );

            $this->logActivity('upload_evidence', "Usuario subió {$uploadedFiles->count()} archivos de evidencia para {$modelType} ID: {$modelId}");

            return response()->json([
                'message' => 'Medios de prueba subidos exitosamente',
                'files' => $uploadedFiles->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                        'size' => $file->getSizeForHumans(),
                        'url' => $this->fileService->getFileUrl($file),
                        'uploaded_at' => $file->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir archivos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Subir archivo de firma para una entidad.
     * @param Request $request
     * @param string $modelType
     * @param int $modelId
     * @return JsonResponse
     */
    public function uploadSignature(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $request->validate([
            'file' => File::getValidationRules(File::TYPE_SIGNATURE),
            'base_folder' => 'sometimes|string',
            'disk' => 'sometimes|string',
            'custom_name' => 'sometimes|string|nullable',
        ]);

        try {
            // Obtener el modelo dinámicamente
            $modelClass = 'App\\Models\\' . ucfirst($modelType);
            if (!class_exists($modelClass)) {
                return response()->json(['message' => 'Modelo no válido'], 400);
            }

            $model = $modelClass::findOrFail($modelId);

            $files = $this->fileService->uploadFiles(
                $model,
                [$request->file('file')],
                File::TYPE_SIGNATURE,
                [
                    'disk' => $request->input('disk', 'public'),
                    'replaceExisting' => true,
                    'customNames' => [$request->input('custom_name')],
                    'baseFolder' => $request->input('base_folder', ''),
                ]
            );

            $uploadedFile = $files->first();

            $this->logActivity('upload_signature', "Usuario subió firma para {$modelType} ID: {$modelId}");

            return response()->json([
                'message' => 'Firma subida exitosamente',
                'file' => [
                    'id' => $uploadedFile->id,
                    'original_name' => $uploadedFile->original_name,
                    'size' => $uploadedFile->getSizeForHumans(),
                    'url' => $this->fileService->getFileUrl($uploadedFile),
                    'uploaded_at' => $uploadedFile->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir firma',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Subir archivo temporal (para Dropzone/FilePond).
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadTemporary(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'file_type' => 'required|in:evidence,signature',
            'session_id' => 'required|string',
        ]);

        try {
            $temporaryFile = $this->fileService->uploadTemporaryFile(
                $request->input('session_id'),
                $request->file('file'),
                $request->input('file_type')
            );

            $this->logActivity('upload_temporary', "Usuario subió archivo temporal: {$temporaryFile->original_name}");

            return response()->json([
                'message' => 'Archivo temporal subido exitosamente',
                'file' => [
                    'id' => $temporaryFile->id,
                    'original_name' => $temporaryFile->original_name,
                    'name' => $temporaryFile->original_name,
                    'display_name' => $temporaryFile->original_name,
                    'filename' => basename($temporaryFile->path),
                    'size' => $temporaryFile->getSizeForHumans(),
                    'url' => $temporaryFile->getUrl(),
                    'file_type' => $temporaryFile->file_type,
                    'uploaded_at' => $temporaryFile->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir archivo temporal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener archivos temporales por session_id.
     * @param Request $request
     * @return JsonResponse
     */
    public function getTemporaryFiles(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $files = $this->fileService->getTemporaryFiles($request->input('session_id'));

            return response()->json([
                'files' => $files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                        'name' => $file->original_name,
                        'display_name' => $file->original_name,
                        'filename' => basename($file->path),
                        'size' => $file->getSizeForHumans(),
                        'url' => $file->getUrl(),
                        'file_type' => $file->file_type,
                        'uploaded_at' => $file->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener archivos temporales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar archivo temporal.
     * @param TemporaryFile $temporaryFile
     * @return JsonResponse
     */
    public function deleteTemporary(TemporaryFile $temporaryFile): JsonResponse
    {
        try {
            $this->fileService->deleteTemporaryFile($temporaryFile);
            $this->logActivity('delete_temporary', "Usuario eliminó archivo temporal: {$temporaryFile->original_name}");

            return response()->json([
                'message' => 'Archivo temporal eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar archivo temporal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descargar archivo genérico (PDF, Word, Excel, imágenes, etc.).
     */
    public function download(int $id)
    {
        try {
            // Validar que el archivo existe en la base de datos por ID
            $file = $this->fileService->getFileById($id);

            // Verificar que el archivo físico existe en el disco
            if (!$file->exists()) {
                return response()->json([
                    'error' => 'Archivo no encontrado',
                    'message' => 'El archivo no existe en el servidor'
                ], Response::HTTP_NOT_FOUND, [
                    'X-Download-Message' => 'El archivo no existe en el servidor',
                    'X-Download-Status' => 'error',
                    'X-Error-Type' => 'file_not_found'
                ]);
            }

            // Obtener el contenido del archivo desde el disco
            $fileContent = Storage::disk($file->disk)->get($file->path);

            $this->logActivity('download_file', 'Usuario descargó archivo - Nombre: ' . $file->original_name . ', Tipo: ' . $file->mime_type);

            $filename = $this->fileService->getFilename($file);

            return response($fileContent, 200, [
                'Content-Type' => $file->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($fileContent),
                'Cache-Control' => 'no-cache, private',
                'X-Download-Message' => 'Archivo ' . $file->original_name . ' descargado correctamente',
                'X-Download-Status' => 'success'
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error al descargar el archivo', ['file_id' => $id, 'ip' => request()->ip(), 'user_agent' => request()->userAgent()]);
        }
    }

    /**
     * Helper para manejar excepciones de forma consistente.
     */
    private function handleException(Throwable $e, string $userMessage, array $context = []): JsonResponse
    {
        Log::error($e->getMessage(), array_merge($context, [
            'user_id' => auth()->id(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]));

        $statusCode = $e instanceof ValidationException ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'error' => $userMessage,
            'message' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
        ], $statusCode, [
            'X-Download-Message' => $userMessage,
            'X-Download-Status' => 'error',
            'X-Error-Type' => $e instanceof ValidationException ? 'validation_error' : 'server_error'
        ]);
    }
}
