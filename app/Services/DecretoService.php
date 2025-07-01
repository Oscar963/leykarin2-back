<?php

namespace App\Services;

use App\Models\Decreto;
use App\Models\PurchasePlan;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DecretoService
{
    /**
     * Obtiene todos los decretos
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDecretos()
    {
        return Decreto::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene decretos paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllDecretosByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Decreto::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un decreto por su ID
     *
     * @param int $id ID del decreto
     * @return Decreto
     */
    public function getDecretoById($id)
    {
        return Decreto::findOrFail($id);
    }

    /**
     * Crea un nuevo decreto
     *
     * @param array $data Datos del decreto
     * @return Decreto
     */
    public function createDecreto(array $data)
    {
        $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
        $direction = $purchasePlan->direction;
        $currentDate = now()->format('Y-m-d H:i');
        $nameFile = $data['name_file'] ?? "{$currentDate} - {$direction->name} - Decreto";

        $decreto = new Decreto();
        $decreto->name = trim($nameFile);
        $decreto->description = isset($data['description']) ? trim($data['description']) : "Decreto generado automáticamente para el plan de compra de la dirección {$direction->name}";

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            $decreto->size = $data['file']->getSize();
            $decreto->type = $data['file']->getClientMimeType();

            // Generar nombre único para el archivo
            $fileName = Str::slug($nameFile) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            // Almacenar archivo en el disco público
            $filePath = $data['file']->storeAs('uploads/decretos', $fileName, 'public');
            // Generar URL pública del archivo
            $decreto->url = url('storage/' . $filePath);
        }

        $decreto->created_by = auth()->id();
        $decreto->save();

        $purchasePlan->decreto_id = $decreto->id;
        $purchasePlan->save();

        return $decreto;
    }

    /**
     * Actualiza un decreto existente
     *
     * @param int $id ID del decreto
     * @param array $data Datos actualizados
     * @return Decreto
     */
    public function updateDecreto($id, array $data)
    {
        $decreto = $this->getDecretoById($id);
        $decreto->name = trim($data['name']);
        $decreto->description = isset($data['description']) ? trim($data['description']) : $decreto->description;
        $decreto->url = isset($data['url']) ? trim($data['url']) : $decreto->url;
        $decreto->type = isset($data['type']) ? trim($data['type']) : $decreto->type;
        $decreto->size = isset($data['size']) ? $data['size'] : $decreto->size;

        $decreto->updated_by = auth()->id();
        $decreto->save();

        return $decreto;
    }

    /**
     * Elimina un decreto
     *
     * @param int $id ID del decreto
     * @return void
     */
    public function deleteDecreto($id)
    {
        $decreto = $this->getDecretoById($id);
        $purchasePlan = PurchasePlan::where('decreto_id', $id)->first();
        if ($purchasePlan) {
            $purchasePlan->decreto_id = null;
            $purchasePlan->save();
        }
        $decreto->delete();
    }

    /**
     * Descarga un archivo de decreto
     *
     * @param int $id ID del decreto
     * @return BinaryFileResponse
     */
    public function downloadFile(int $id): BinaryFileResponse
    {
        $decreto = $this->getDecretoById($id);
        $filePath = str_replace(url('storage/'), '', $decreto->url);
        return response()->download(storage_path("app/public/{$filePath}"), $decreto->name);
    }
} 