<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TemporaryFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'file_type',
        'original_name',
        'path',
        'disk',
        'size',
        'mime_type',
        'extension',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'expires_at' => 'datetime',
    ];

    // Tipos de archivos permitidos
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_SIGNATURE = 'signature';

    /**
     * Scope para archivos no expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope para archivos expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Verificar si el archivo temporal está expirado
     */
    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    /**
     * Scope por session_id
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope por tipo de archivo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Verificar si el archivo existe en el disco
     */
    public function exists()
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Obtener URL del archivo temporal
     */
    public function getUrl()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */

        $disk = Storage::disk($this->disk);
        
        if (method_exists($disk, 'url')) {
            return $disk->url($this->path);
        }
        
        return route('temporary-files.download', $this->id);
    }

    /**
     * Eliminar archivo temporal
     */
    public function delete()
    {
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }
        
        return parent::delete();
    }

    /**
     * Convertir a archivo permanente
     */
    public function convertToPermanent($fileable, $displayName = null)
    {
        // Definir nueva ruta para archivo permanente
        // Preferir estructura con token de denuncia si está disponible: {token}/{file_type}
        $hasToken = is_object($fileable) && method_exists($fileable, 'getAttribute') && !empty($fileable->getAttribute('token'));
        if ($hasToken) {
            $newDirectory = $fileable->getAttribute('token') . '/' . $this->file_type; // p.ej. ABC123/evidence
        } else {
            $modelName = strtolower(class_basename($fileable));
            $newDirectory = $modelName . 's/' . $fileable->id . '/' . $this->file_type;
        }
        $fileName = pathinfo($this->path, PATHINFO_BASENAME);
        $newPath = $newDirectory . '/' . $fileName;

        // Mover archivo de carpeta temporal a carpeta definitiva
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->move($this->path, $newPath);
        }

        // Crear archivo permanente con nueva ruta
        $permanentFile = $fileable->files()->create([
            'file_type' => $this->file_type,
            'original_name' => $this->original_name,
            'display_name' => $displayName,
            'path' => $newPath,
            'disk' => $this->disk,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'metadata' => array_merge($this->metadata ?? [], [
                'moved_from_temp' => $this->path,
                'converted_at' => now()->toISOString(),
            ]),
        ]);

        // Eliminar registro temporal
        parent::delete();

        return $permanentFile;
    }

    /**
     * Obtener tamaño legible
     */
    public function getSizeForHumans()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
