<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class FileValidation implements Rule
{
    protected $allowedMimes;
    protected $maxSize;
    protected $allowedExtensions;

    public function __construct(array $allowedMimes = [], int $maxSize = null, array $allowedExtensions = [])
    {
        // Usar variables de entorno para configuración por defecto
        $this->allowedMimes = $allowedMimes ?: $this->getDefaultMimes();
        $this->maxSize = $maxSize ?: $this->getDefaultMaxSize();
        $this->allowedExtensions = $allowedExtensions ?: $this->getDefaultExtensions();
    }

    protected function getDefaultMimes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
    }

    protected function getDefaultMaxSize(): int
    {
        // Usar variable de entorno o valor por defecto (10MB)
        return (int) env('MAX_FILE_SIZE_DOCUMENT', 10240);
    }

    protected function getDefaultExtensions(): array
    {
        // Usar variable de entorno o extensiones por defecto
        $extensions = env('ALLOWED_FILE_EXTENSIONS_DOCUMENT', 'doc,docx,pdf,jpg,jpeg,png,gif');
        return array_map('trim', explode(',', $extensions));
    }

    public function passes($attribute, $value)
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        // Verificar tamaño del archivo
        if ($value->getSize() > ($this->maxSize * 1024)) {
            return false;
        }

        // Verificar extensión
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            return false;
        }

        // Verificar MIME type
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimes)) {
            return false;
        }

        // Verificar contenido del archivo (primeros bytes)
        $handle = fopen($value->getPathname(), 'rb');
        $contents = fread($handle, 4);
        fclose($handle);

        // Verificar firmas de archivos conocidas
        $signatures = [
            'pdf' => ['%PDF'],
            'doc' => ['\xD0\xCF\x11\xE0'],
            'docx' => ['PK'],
            'xls' => ['\xD0\xCF\x11\xE0'],
            'xlsx' => ['PK'],
            'jpg' => ['\xFF\xD8\xFF'],
            'jpeg' => ['\xFF\xD8\xFF'],
            'png' => ['\x89PNG'],
            'gif' => ['GIF87a', 'GIF89a']
        ];

        if (isset($signatures[$extension])) {
            $validSignature = false;
            foreach ($signatures[$extension] as $signature) {
                if (strpos($contents, $signature) === 0) {
                    $validSignature = true;
                    break;
                }
            }
            if (!$validSignature) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'El archivo no es válido. Verifique el formato, tamaño y contenido del archivo.';
    }
} 