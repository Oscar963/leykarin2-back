<?php

namespace App\Services;

use App\Models\Evidence;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvidenceService
{

    public function downloadFile(int $id): BinaryFileResponse
    {
        $file = Evidence::findOrFail($id);
        $filePath = str_replace(url('storage/'), '', $file->url);

        if (!Storage::disk('public')->exists($filePath)) {
            throw new Exception("El archivo no existe en el servidor.");
        }

        $fileName = pathinfo($file->name, PATHINFO_FILENAME);
        $extension = pathinfo($file->name, PATHINFO_EXTENSION);

        // Convertir caracteres especiales a ASCII (Ej: "Instrucción" → "Instruccion")
        $fileName = Str::ascii($fileName);

        // Reemplazar caracteres problemáticos
        $fileName = preg_replace('/[\/\\:*?"<>|&\'~]/', '_', $fileName);

        // Remover caracteres invisibles o erróneos
        $fileName = trim(str_replace(["\r", "\n", "\t", "\0", "\x0B"], '', $fileName));

        // Reemplazar espacios múltiples por un solo guion bajo
        $fileName = preg_replace('/\s+/', '_', $fileName);

        $safeFileName = $fileName . '.' . $extension;

        return response()->download(storage_path("app/public/{$filePath}"), $safeFileName);
    }
}
