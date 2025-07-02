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

        // ✅ CAMBIO AUTOMÁTICO DE ESTADO A "DECRETADO"
        $this->updatePurchasePlanStatusToDecretado($purchasePlan);

        return $decreto;
    }

    /**
     * Actualiza el estado del plan de compra a "Decretado" cuando se crea un decreto
     *
     * @param PurchasePlan $purchasePlan
     * @return void
     */
    private function updatePurchasePlanStatusToDecretado(PurchasePlan $purchasePlan)
    {
        // Obtener el estado actual
        $currentStatus = $purchasePlan->getCurrentStatus();
        $currentStatusName = $currentStatus && $currentStatus->status ? $currentStatus->status->name : 'Desconocido';

        // Solo cambiar si NO está ya en estado "Decretado" o "Publicado"
        if ($currentStatus && !in_array($currentStatus->status_purchase_plan_id, [6, 7])) {
            // Crear nuevo estado "Decretado" (ID: 6)
            $purchasePlanStatus = new \App\Models\PurchasePlanStatus();
            $purchasePlanStatus->purchase_plan_id = $purchasePlan->id;
            $purchasePlanStatus->status_purchase_plan_id = 6; // ID del estado "Decretado"
            $purchasePlanStatus->sending_date = now();
            $purchasePlanStatus->sending_comment = 'Estado cambiado automáticamente a "Decretado" al crear el decreto';
            $purchasePlanStatus->created_by = auth()->id();
            $purchasePlanStatus->save();

            // Registrar en el historial
            \App\Models\HistoryPurchaseHistory::logAction(
                $purchasePlan->id,
                'status_change',
                "Estado cambiado de '{$currentStatusName}' a 'Decretado' al crear decreto",
                [
                    'old_status' => [
                        'id' => $currentStatus->status_purchase_plan_id,
                        'name' => $currentStatusName
                    ],
                    'new_status' => [
                        'id' => 6,
                        'name' => 'Decretado'
                    ],
                    'comment' => 'Estado cambiado automáticamente al crear el decreto'
                ]
            );
        }
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
            // ✅ CAMBIO AUTOMÁTICO DE ESTADO A "APROBADO PARA DECRETAR"
            $this->revertPurchasePlanStatusToApproved($purchasePlan);

            $purchasePlan->decreto_id = null;
            $purchasePlan->save();
        }

        $decreto->delete();
    }

    /**
     * Revierte el estado del plan de compra a "Aprobado para decretar" cuando se elimina un decreto
     *
     * @param PurchasePlan $purchasePlan
     * @return void
     */
    private function revertPurchasePlanStatusToApproved(PurchasePlan $purchasePlan)
    {
        // Obtener el estado actual
        $currentStatus = $purchasePlan->getCurrentStatus();
        $currentStatusName = $currentStatus && $currentStatus->status ? $currentStatus->status->name : 'Desconocido';

        // Solo cambiar si está en estado "Decretado" o "Publicado"
        if ($currentStatus && in_array($currentStatus->status_purchase_plan_id, [6, 7])) {
            // Crear nuevo estado "Aprobado para decretar" (ID: 4)
            $purchasePlanStatus = new \App\Models\PurchasePlanStatus();
            $purchasePlanStatus->purchase_plan_id = $purchasePlan->id;
            $purchasePlanStatus->status_purchase_plan_id = 4; // ID del estado "Aprobado para decretar"
            $purchasePlanStatus->sending_date = now();
            $purchasePlanStatus->sending_comment = 'Estado revertido automáticamente a "Aprobado para decretar" al eliminar el decreto';
            $purchasePlanStatus->created_by = auth()->id();
            $purchasePlanStatus->save();

            // Registrar en el historial
            \App\Models\HistoryPurchaseHistory::logAction(
                $purchasePlan->id,
                'status_change',
                "Estado revertido de '{$currentStatusName}' a 'Aprobado para decretar' al eliminar decreto",
                [
                    'old_status' => [
                        'id' => $currentStatus->status_purchase_plan_id,
                        'name' => $currentStatusName
                    ],
                    'new_status' => [
                        'id' => 4,
                        'name' => 'Aprobado para decretar'
                    ],
                    'comment' => 'Estado revertido automáticamente al eliminar el decreto'
                ]
            );
        }
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
