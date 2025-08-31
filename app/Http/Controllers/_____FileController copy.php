<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Complaint;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class _FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Subir archivos de evidencia para una denuncia
     */
    public function uploadEvidence(Request $request, $complaintId): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => File::getValidationRules(File::TYPE_EVIDENCE),
        ]);

        try {
            $complaint = Complaint::findOrFail($complaintId);
            
            DB::beginTransaction();
            
            $uploadedFiles = $this->fileService->uploadMultipleFiles(
                $complaint,
                $request->file('files'),
                File::TYPE_EVIDENCE,
                'public'
            );
            
            DB::commit();

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
            DB::rollBack();
            return response()->json([
                'message' => 'Error al subir archivos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Subir archivo de firma para una denuncia
     */
    public function uploadSignature(Request $request, $complaintId): JsonResponse
    {
        $request->validate([
            'file' => File::getValidationRules(File::TYPE_SIGNATURE),
        ]);

        try {
            $complaint = Complaint::findOrFail($complaintId);
            
            // Eliminar firma anterior si existe
            $complaint->deleteFilesByType(File::TYPE_SIGNATURE);
            
            DB::beginTransaction();
            
            $uploadedFile = $this->fileService->uploadFile(
                $complaint,
                $request->file('file'),
                File::TYPE_SIGNATURE,
                'public'
            );
            
            DB::commit();

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
            DB::rollBack();
            return response()->json([
                'message' => 'Error al subir firma',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener archivos de una denuncia
     */
    public function getComplaintFiles($complaintId): JsonResponse
    {
        try {
            $complaint = Complaint::with('files')->findOrFail($complaintId);
            
            $files = $complaint->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_type' => $file->file_type,
                    'original_name' => $file->original_name,
                    'size' => $file->getSizeForHumans(),
                    'mime_type' => $file->mime_type,
                    'url' => $this->fileService->getFileUrl($file),
                    'uploaded_at' => $file->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'files' => $files,
                'evidence_count' => $complaint->countFilesByType(File::TYPE_EVIDENCE),
                'signature_count' => $complaint->countFilesByType(File::TYPE_SIGNATURE),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener archivos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar archivo específico
     */
    public function deleteFile($fileId): JsonResponse
    {
        try {
            $file = File::findOrFail($fileId);
            
            $this->fileService->deleteFile($file);

            return response()->json([
                'message' => 'Archivo eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descargar archivo
     */
    public function downloadFile($fileId)
    {
        try {
            $file = File::findOrFail($fileId);
            
            if (!$file->exists()) {
                return response()->json([
                    'message' => 'Archivo no encontrado en el sistema de archivos',
                ], 404);
            }

            return Storage::disk($file->disk)->download($file->path, $file->original_name);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al descargar archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
