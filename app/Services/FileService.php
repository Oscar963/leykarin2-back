<?php

namespace App\Services;

use App\Models\File;
use App\Models\TemporaryFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FileService
{
    /**
     * Obtiene todos los archivos ordenados por fecha de creación (descendente).
     *
     * @return Collection<File>
     */
    public function getAllFiles(): Collection
    {
        return File::latest()->get();
    }

    /**
     * Obtiene todos los archivos con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<File>
     */
    public function getAllFilesByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return File::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo archivo usando asignación masiva.
     *
     * @param array $data
     * @return File
     */
    public function createFile(array $data): File
    {
        return File::create($data);
    }

    /**
     * Obtiene un archivo por su ID.
     *
     * @param int $id
     * @return File
     */
    public function getFileById(int $id): File
    {
        return File::findOrFail($id);
    }

    /**
     * Actualiza un archivo usando asignación masiva.
     *
     * @param File $file
     * @param array $data
     * @return File
     */
    public function updateFile(File $file, array $data): File
    {
        $file->update($data);
        return $file;
    }

    /**
     * Elimina un archivo.
     *
     * @param File $file
     * @return File
     */
    public function deleteFile(File $file): File
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $file->delete();
        return $file;
    }

    /**
     * Sube múltiples archivos de evidencia para una entidad.
     *
     * @param mixed $fileable
     * @param array $uploadedFiles
     * @param string $disk
     * @return Collection<File>
     */
    public function uploadEvidence($fileable, array $uploadedFiles, string $disk = 'public'): Collection
    {
        return $this->uploadFiles($fileable, $uploadedFiles, File::TYPE_EVIDENCE, ['disk' => $disk]);
    }

    /**
     * Sube un archivo de firma para una entidad.
     *
     * @param mixed $fileable
     * @param UploadedFile $uploadedFile
     * @param string $disk
     * @return File
     */
    public function uploadSignature($fileable, UploadedFile $uploadedFile, string $disk = 'public'): File
    {
        $result = $this->uploadFiles($fileable, [$uploadedFile], File::TYPE_SIGNATURE, [
            'disk' => $disk,
            'replaceExisting' => true
        ]);
        
        return $result->first();
    }

    /**
     * Método genérico para subir múltiples archivos con transacciones.
     *
     * @param mixed $fileable
     * @param array $files Array de UploadedFile
     * @param string $fileType
     * @param array $options Opciones adicionales: disk, replaceExisting, customNames
     * @return Collection<File>
     * @throws \Exception
     */
    public function uploadFiles($fileable, array $files, string $fileType, array $options = []): Collection
    {
        $disk = $options['disk'] ?? 'public';
        $replaceExisting = $options['replaceExisting'] ?? false;
        $customNames = $options['customNames'] ?? [];
        
        return DB::transaction(function () use ($fileable, $files, $fileType, $disk, $replaceExisting, $customNames, $options) {
            $uploadedFiles = collect();
            
            try {
                // Si se debe reemplazar archivos existentes del mismo tipo
                if ($replaceExisting) {
                    $fileable->files()->where('file_type', $fileType)->each(function ($file) {
                        $this->deleteFile($file);
                    });
                }
                
                // Subir cada archivo
                foreach ($files as $index => $file) {
                    $uploadedFile = $this->uploadFile($fileable, $file, $fileType, $disk, $options);
                    
                    // Aplicar nombre personalizado si se proporciona
                    if (isset($customNames[$index]) && !empty($customNames[$index])) {
                        $uploadedFile->update(['display_name' => $customNames[$index]]);
                    }
                    
                    $uploadedFiles->push($uploadedFile);
                }
                
                return $uploadedFiles;
                
            } catch (\Exception $e) {
                // En caso de error, la transacción se revierte automáticamente
                Log::error('Error uploading files: ' . $e->getMessage(), [
                    'fileable_type' => get_class($fileable),
                    'fileable_id' => $fileable->id ?? null,
                    'file_type' => $fileType,
                    'files_count' => count($files)
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Valida un archivo antes de subirlo.
     *
     * @param UploadedFile $file
     * @param string $fileType
     * @return void
     * @throws ValidationException
     */
    private function validateFile(UploadedFile $file, string $fileType): void
    {
        // Obtener reglas de validación del modelo File
        $rules = File::getValidationRules($fileType);
        
        // Validar tamaño máximo
        $maxSize = $this->getMaxSizeFromRules($rules);
        if ($file->getSize() > $maxSize * 1024) { // Convertir KB a bytes
            throw ValidationException::withMessages([
                'file' => "El archivo excede el tamaño máximo permitido de {$maxSize}KB."
            ]);
        }
        
        // Validar extensión
        $allowedMimes = $this->getAllowedMimesFromRules($rules);
        if (!in_array($file->getClientOriginalExtension(), $allowedMimes)) {
            throw ValidationException::withMessages([
                'file' => 'El tipo de archivo no está permitido.'
            ]);
        }
        
        // Validar que el archivo no esté corrupto
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'file' => 'El archivo está corrupto o no se pudo procesar.'
            ]);
        }
    }
    
    /**
     * Extrae el tamaño máximo de las reglas de validación.
     */
    private function getMaxSizeFromRules(array $rules): int
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && strpos($rule, 'max:') === 0) {
                return (int) substr($rule, 4);
            }
        }
        return 10240; // Default 10MB
    }
    
    /**
     * Extrae las extensiones permitidas de las reglas de validación.
     */
    private function getAllowedMimesFromRules(array $rules): array
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && strpos($rule, 'mimes:') === 0) {
                return explode(',', substr($rule, 6));
            }
        }
        return ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']; // Default
    }

    /**
     * Sube un archivo individual.
     *
     * @param mixed $fileable
     * @param UploadedFile $uploadedFile
     * @param string $fileType
     * @param string $disk
     * @return File
     */
    private function uploadFile($fileable, UploadedFile $uploadedFile, string $fileType, string $disk = 'public', array $options = []): File
    {
        // Validar archivo antes de subirlo
        $this->validateFile($uploadedFile, $fileType);
        
        // Generar nombre único para el archivo
        $fileName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        
        // Definir directorio basado en el token de la denuncia si existe, con subcarpeta por tipo
        // Estructura: {token}/{evidence|signature}
        $hasToken = is_object($fileable) && method_exists($fileable, 'getAttribute') && !empty($fileable->getAttribute('token'));
        if ($hasToken) {
            $baseFolder = trim((string)($options['baseFolder'] ?? ''), '/');
            $prefix = $baseFolder !== '' ? ($baseFolder . '/') : '';
            $directory = $prefix . $fileable->getAttribute('token') . '/' . $fileType; // p.ej. Complaint/ABC123/evidence
        } else {
            // Fallback a estructura por modelo/id si no hay token disponible
            $directory = strtolower(class_basename($fileable)) . 's/' . $fileable->id . '/' . $fileType;
        }
        
        // Subir archivo
        $path = $uploadedFile->storeAs($directory, $fileName, $disk);
        
        // Crear registro en base de datos
        return $fileable->files()->create([
            'file_type' => $fileType,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'metadata' => [
                'uploaded_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Obtiene la URL de un archivo.
     *
     * @param File $file
     * @return string
     */
    public function getFileUrl(File $file): string
    {
        return $file->getUrl();
    }

    /**
     * Sube un archivo temporal durante el llenado del formulario.
     *
     * @param string $sessionId
     * @param UploadedFile $uploadedFile
     * @param string $fileType
     * @param string $disk
     * @return TemporaryFile
     */
    public function uploadTemporaryFile(string $sessionId, UploadedFile $uploadedFile, string $fileType, string $disk = 'public'): TemporaryFile
    {
        // Generar nombre único
        $fileName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        
        // Directorio temporal
        $directory = 'temp/' . $sessionId . '/' . $fileType;
        
        // Subir archivo
        $path = $uploadedFile->storeAs($directory, $fileName, $disk);
        
        // Crear registro temporal (expira en 2 horas)
        return TemporaryFile::create([
            'session_id' => $sessionId,
            'file_type' => $fileType,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'expires_at' => now()->addHours(2),
            'metadata' => [
                'uploaded_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Obtiene archivos temporales por session_id.
     *
     * @param string $sessionId
     * @return Collection<TemporaryFile>
     */
    public function getTemporaryFiles(string $sessionId): Collection
    {
        return TemporaryFile::bySession($sessionId)->notExpired()->get();
    }

    /**
     * Confirma archivos temporales y los convierte a permanentes.
     *
     * @param string $sessionId
     * @param mixed $fileable
     * @param array $customNames Array con nombres personalizados indexados por temp_file_id
     * @return Collection<File>
     */
    public function confirmTemporaryFiles(string $sessionId, $fileable, array $customNames = []): Collection
    {
        $temporaryFiles = $this->getTemporaryFiles($sessionId);
        $permanentFiles = collect();

        foreach ($temporaryFiles as $tempFile) {
            $displayName = $customNames[$tempFile->id] ?? null;
            $permanentFile = $tempFile->convertToPermanent($fileable, $displayName);
            $permanentFiles->push($permanentFile);
        }

        return $permanentFiles;
    }

    /**
     * Procesa archivos temporales específicos del formulario y los convierte a permanentes.
     *
     * @param array $tempFileIds Array de IDs de archivos temporales
     * @param mixed $fileable Modelo al que se asociarán los archivos
     * @param array $customNames Array con nombres personalizados indexados por temp_file_id
     * @return Collection<File>
     */
    public function processFormFiles(array $tempFileIds, $fileable, array $customNames = []): Collection
    {
        $permanentFiles = collect();

        foreach ($tempFileIds as $tempFileId) {
            $tempFile = TemporaryFile::find($tempFileId);
            
            if ($tempFile && !$tempFile->isExpired()) {
                $displayName = $customNames[$tempFileId] ?? null;
                $permanentFile = $tempFile->convertToPermanent($fileable, $displayName);
                $permanentFiles->push($permanentFile);
            }
        }

        return $permanentFiles;
    }

    /**
     * Elimina un archivo temporal.
     *
     * @param TemporaryFile $temporaryFile
     * @return bool
     */
    public function deleteTemporaryFile(TemporaryFile $temporaryFile): bool
    {
        try {
            if ($temporaryFile->exists()) {
                Storage::disk($temporaryFile->disk)->delete($temporaryFile->path);
            }
            
            return (bool) $temporaryFile->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting temporary file: ' . $e->getMessage(), [
                'file_id' => $temporaryFile->id,
                'path' => $temporaryFile->path,
                'disk' => $temporaryFile->disk
            ]);
            
            throw $e;
        }
    }

    /**
     * Limpia archivos temporales expirados.
     *
     * @return int
     */
    public function cleanupExpiredTemporaryFiles(): int
    {
        $expiredFiles = TemporaryFile::expired()->get();
        $count = 0;

        foreach ($expiredFiles as $file) {
            if ($file->delete()) {
                $count++;
            }
        }

        return $count;
    }
}
