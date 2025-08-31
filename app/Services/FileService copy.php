<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class FileService
{
    /**
     * Subir archivo con validaciones
     */
    public function uploadFile($fileable, UploadedFile $uploadedFile, $fileType, $disk = 'public', $metadata = [])
    {
        // Validar archivo según tipo
        $rules = File::getValidationRules($fileType);
        $validator = Validator::make(['file' => $uploadedFile], ['file' => $rules]);
        
        if ($validator->fails()) {
            throw new \InvalidArgumentException('Archivo no válido: ' . $validator->errors()->first());
        }

        // Generar nombre único
        $extension = $uploadedFile->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Definir directorio basado en tipo y modelo
        $directory = $this->getFileDirectory($fileable, $fileType);
        
        // Subir archivo
        $path = $uploadedFile->storeAs($directory, $filename, $disk);

        // Crear registro en BD
        return $fileable->files()->create([
            'file_type' => $fileType,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $extension,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Subir múltiples archivos
     */
    public function uploadMultipleFiles($fileable, array $files, $fileType, $disk = 'public', $metadata = [])
    {
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = $this->uploadFile($fileable, $file, $fileType, $disk, $metadata);
            }
        }
        
        return $uploadedFiles;
    }

    /**
     * Obtener URL pública del archivo
     */
    public function getFileUrl(File $file)
    {
        if ($file->disk === 'public') {
            return Storage::disk('public')->url($file->path);
        }
        
        // Para discos privados, generar URL temporal
        return Storage::disk($file->disk)->temporaryUrl($file->path, now()->addHours(1));
    }

    /**
     * Eliminar archivo
     */
    public function deleteFile(File $file)
    {
        // Eliminar archivo físico
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        
        // Eliminar registro de BD
        return $file->delete();
    }

    /**
     * Obtener directorio para archivos
     */
    protected function getFileDirectory($fileable, $fileType)
    {
        $modelName = strtolower(class_basename($fileable));
        $modelId = $fileable->id ?? 'temp';
        
        switch ($fileType) {
            case File::TYPE_EVIDENCE:
                return "files/{$modelName}/{$modelId}/evidence";
            case File::TYPE_SIGNATURE:
                return "files/{$modelName}/{$modelId}/signatures";
            default:
                return "files/{$modelName}/{$modelId}/misc";
        }
    }

    /**
     * Validar archivos antes de subir
     */
    public function validateFiles(array $files, $fileType)
    {
        $rules = File::getValidationRules($fileType);
        $errors = [];
        
        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $validator = Validator::make(['file' => $file], ['file' => $rules]);
                
                if ($validator->fails()) {
                    $errors["file_{$index}"] = $validator->errors()->first();
                }
            }
        }
        
        return $errors;
    }
}
