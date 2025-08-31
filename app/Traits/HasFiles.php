<?php

namespace App\Traits;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFiles
{
    /**
     * Relación polimórfica con archivos
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Obtener archivos por tipo
     */
    public function getFilesByType($type)
    {
        return $this->files()->where('file_type', $type)->get();
    }

    /**
     * Obtener medios de prueba
     */
    public function getEvidenceFiles()
    {
        return $this->getFilesByType(File::TYPE_EVIDENCE);
    }

    /**
     * Obtener archivos de firma
     */
    public function getSignatureFiles()
    {
        return $this->getFilesByType(File::TYPE_SIGNATURE);
    }

    /**
     * Subir archivo y crear registro
     */
    public function uploadFile(UploadedFile $uploadedFile, $fileType, $disk = 'local', $metadata = [])
    {
        // Validar tipo de archivo
        if (!in_array($fileType, File::ALLOWED_TYPES)) {
            throw new \InvalidArgumentException("Tipo de archivo no válido: {$fileType}");
        }

        // Generar nombre único
        $extension = $uploadedFile->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Definir directorio basado en tipo
        $directory = $this->getFileDirectory($fileType);
        $path = $directory . '/' . $filename;

        // Subir archivo
        $storedPath = $uploadedFile->storeAs($directory, $filename, $disk);

        // Crear registro en BD
        return $this->files()->create([
            'file_type' => $fileType,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $storedPath,
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
    public function uploadFiles(array $files, $fileType, $disk = 'local', $metadata = [])
    {
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = $this->uploadFile($file, $fileType, $disk, $metadata);
            }
        }
        
        return $uploadedFiles;
    }

    /**
     * Eliminar archivo por ID
     */
    public function deleteFile($fileId)
    {
        $file = $this->files()->find($fileId);
        
        if ($file) {
            return $file->delete();
        }
        
        return false;
    }

    /**
     * Eliminar todos los archivos de un tipo
     */
    public function deleteFilesByType($fileType)
    {
        $files = $this->getFilesByType($fileType);
        
        foreach ($files as $file) {
            $file->delete();
        }
        
        return true;
    }

    /**
     * Obtener directorio basado en tipo de archivo
     */
    protected function getFileDirectory($fileType)
    {
        $modelName = strtolower(class_basename($this));
        
        switch ($fileType) {
            case File::TYPE_EVIDENCE:
                return "files/{$modelName}/evidence";
            case File::TYPE_SIGNATURE:
                return "files/{$modelName}/signatures";
            default:
                return "files/{$modelName}/misc";
        }
    }

    /**
     * Verificar si tiene archivos de un tipo específico
     */
    public function hasFilesOfType($fileType)
    {
        return $this->files()->where('file_type', $fileType)->exists();
    }

    /**
     * Contar archivos por tipo
     */
    public function countFilesByType($fileType)
    {
        return $this->files()->where('file_type', $fileType)->count();
    }
}
