<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'file_type',
        'original_name',
        'display_name',
        'path',
        'disk',
        'size',
        'mime_type',
        'extension',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    // Tipos de archivos permitidos
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_SIGNATURE = 'signature';

    const ALLOWED_TYPES = [
        self::TYPE_EVIDENCE,
        self::TYPE_SIGNATURE,
    ];

    // Relación polimórfica
    public function fileable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeEvidence($query)
    {
        return $query->where('file_type', self::TYPE_EVIDENCE);
    }

    public function scopeSignature($query)
    {
        return $query->where('file_type', self::TYPE_SIGNATURE);
    }

    // Métodos helper
    public function getUrl()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */

        $disk = Storage::disk($this->disk);

        if (method_exists($disk, 'url')) {
            return $disk->url($this->path);
        }

        return route('files.download', $this->id);
    }

    public function getFullPath()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */

        $disk = Storage::disk($this->disk);

        if (method_exists($disk, 'path')) {
            return $disk->path($this->path);
        }

        return $this->path;
    }

    public function exists()
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function delete()
    {
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }

        return parent::delete();
    }

    public function getSizeForHumans()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Validaciones para tipos de archivo
    public static function getValidationRules($fileType)
    {
        $baseRules = [
            'required',
            'file',
            'max:10240',    // 10MB
        ];

        switch ($fileType) {
            case self::TYPE_EVIDENCE:
                return array_merge($baseRules, [
                    'mimes:pdf,doc,docx,jpg,jpeg,png,gif,zip,rar',
                    'max:5120', // 5MB
                ]);

            case self::TYPE_SIGNATURE:
                return array_merge($baseRules, [
                    'mimes:jpg,jpeg,png,gif,pdf',
                    'max:2048', // 2MB
                ]);

            default:
                return $baseRules;
        }
    }
}
