<?php

namespace App\Services;

use App\Models\File;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileService
{
    /**
     * Obtiene todos los archivos
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFiles()
    {
        return File::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene archivos paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
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

    /**
     * Obtiene un archivo por su ID
     *
     * @param int $id ID del archivo
     * @return File
     */
    public function getFileById($id)
    {
        return File::findOrFail($id);
    }

    /**
     * Crea un nuevo archivo
     *
     * @param array $data Datos del archivo
     * @return File
     */
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

    /**
     * Actualiza un archivo existente
     *
     * @param int $id ID del archivo
     * @param array $data Datos actualizados
     * @return File
     */
    public function updateFile($id, array $data)
    {
        $file = $this->getFileById($id);

        $file->name = $data['name'];
        $file->description = $data['description'];
        $file->updated_by = auth()->id();

        $file->save();
        return $file;
    }

    /**
     * Elimina un archivo
     *
     * @param int $id ID del archivo
     * @return void
     */
    public function deleteFile($id)
    {
        $file = File::findOrFail($id);
        Storage::disk('public')->delete($file->url);
        $file->delete();
    }

    /**
     * Descarga un archivo
     *
     * @param int $id ID del archivo
     * @return BinaryFileResponse
     * @throws Exception Si el archivo no existe en el servidor
     */
    public function downloadFile(int $id): BinaryFileResponse
    {
        $file = File::findOrFail($id);
        $filePath = str_replace(url('storage/'), '', $file->url);

        if (!Storage::disk('public')->exists($filePath)) {
            throw new Exception("El archivo no existe en el servidor.");
        }

        $safeFileName = $this->generateSafeFileName($file->name);
        return response()->download(storage_path("app/public/{$filePath}"), $safeFileName);
    }

    /**
     * Genera un nombre de archivo seguro para la descarga
     *
     * @param string $fileName Nombre original del archivo
     * @return string Nombre de archivo seguro
     */
    private function generateSafeFileName(string $fileName): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Convertir caracteres especiales a ASCII
        $fileName = Str::ascii($fileName);

        // Reemplazar caracteres problemáticos
        $fileName = preg_replace('/[\/\\:*?"<>|&\'~]/', '_', $fileName);

        // Remover caracteres invisibles o erróneos
        $fileName = trim(str_replace(["\r", "\n", "\t", "\0", "\x0B"], '', $fileName));

        // Reemplazar espacios múltiples por un solo guion bajo
        $fileName = preg_replace('/\s+/', '_', $fileName);

        return $fileName . '.' . $extension;
    }
}
