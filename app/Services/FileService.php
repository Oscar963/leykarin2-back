<?php

namespace App\Services;

use App\Models\File;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileService
{
    public function getAllFiles()
    {
        return File::orderBy('created_at', 'DESC')->get();
    }

    public function getAllFilesByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = File::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }
        return $queryBuilder->paginate($perPage);
    }

    public function createFile(array $data)
    {
        $file = new File();
        $file->name = $data['name'];
        $file->description = $data['description'];
        $file->size = $data['file']->getSize();
        $file->type = $data['file']->getClientMimeType();
        $file->created_by = auth()->id();

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            $fileName = Str::slug($data['name']) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            $filePath = $data['file']->storeAs('uploads', $fileName, 'public');
            $file->url = url('storage/' . $filePath);
        }

        $file->save();
        return $file;
    }

    public function updateFile($id, array $data)
    {
        $file = $this->getFileById($id);

        $file->name = $data['name'];
        $file->description = $data['description'];
        $file->updated_by = auth()->id();

        $file->save();
        return $file;
    }

    public function getFileById($id)
    {
        return File::findOrFail($id);
    }

    public function deleteFile($id)
    {
        $file = File::findOrFail($id);
        Storage::disk('public')->delete($file->url);
        $file->delete();
    }

    public function downloadFile(int $id): BinaryFileResponse
    {
        $file = File::findOrFail($id);
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
