<?php

namespace App\Services;

use App\Models\File;
use App\Models\FormF1;
use App\Models\HistoryPurchaseHistory;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanStatus;
use Illuminate\Support\Str;

/**
 * Servicio para gestión de Planes de Compra
 */
class PurchasePlanService
{
    /**
     * Obtiene todos los planes de compra con paginación y filtrado por nombre
     */
    public function getAllPurchasePlansByQuery(?string $query, int $perPage = 50)
    {
        $queryBuilder = PurchasePlan::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene todos los planes de compra filtrados por año y opcionalmente por nombre
     */
    public function getAllPurchasePlansByYear(int $year, ?string $query = null, int $perPage = 50)
    {
        $queryBuilder = PurchasePlan::orderBy('created_at', 'DESC')
            ->where('year', $year);

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /** 
     * Obtiene todos los planes de compra filtrados por año y opcionalmente por nombre para un usuario específico
     */
    public function getAllPurchasePlansByYearForUser(int $idDirection, int $year)
    {
        $queryBuilder = PurchasePlan::orderBy('created_at', 'DESC')
            ->where('direction_id', $idDirection)
            ->where('year', $year);

        return $queryBuilder->first();
    }

    /**
     * Obtiene un plan de compra por su ID
     */
    public function getPurchasePlanById($id)
    {
        return PurchasePlan::findOrFail($id);
    }

    /**
     * Obtiene un plan de compra por su token único
     */
    public function getPurchasePlanByToken($token)
    {
        return PurchasePlan::where('token', $token)->first();
    }

    /**
     * Obtiene un plan de compra por su año (retorna solo el primero)
     */
    public function getPurchasePlanByYear($year)
    {
        return PurchasePlan::where('year', $year)->first();
    }

    /**
     * Crea un nuevo plan de compra
     */
    public function createPurchasePlan(array $data)
    {
        $direction = auth()->user()->direction;
        $formF1 = $this->createFormF1($data);

        $purchasePlan = new PurchasePlan();
        $purchasePlan->name = $data['name'];
        $purchasePlan->date_created = now();
        $purchasePlan->token = Str::random(32);
        $purchasePlan->year = $data['year'];
        $purchasePlan->form_f1_id = $formF1->id;
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        // Crear el estado inicial (Borrador)
        $this->createPurchasePlanStatus($purchasePlan->id, 1, [
            'sending_comment' => 'Plan de compra creado en estado borrador'
        ]);

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'create',
            'Plan de compra creado',
            [
                'name' => $purchasePlan->name,
                'year' => $purchasePlan->year,
                'direction' => $direction->name,
                'form_f1_id' => $formF1->id
            ]
        );

        return $purchasePlan;
    }

    /**
     * Crea un plan de compra por defecto para un año específico
     */
    public function createDefaultPurchasePlan(int $year): PurchasePlan
    {
        $direction = auth()->user()->direction;

        $purchasePlan = new PurchasePlan();
        $purchasePlan->name = "Plan de Compra {$year} - {$direction->name}";
        $purchasePlan->date_created = now();
        $purchasePlan->token = Str::random(32);
        $purchasePlan->year = $year;
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        // Crear el estado inicial (Borrador)
        $this->createPurchasePlanStatus($purchasePlan->id, 1, [
            'sending_comment' => 'Plan de compra creado automáticamente en estado borrador'
        ]);

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'create_auto',
            'Plan de compra creado automáticamente',
            [
                'name' => $purchasePlan->name,
                'year' => $purchasePlan->year,
                'direction' => $direction->name
            ]
        );

        return $purchasePlan;
    }

    /**
     * Actualiza un plan de compra existente
     */
    public function updatePurchasePlan($id, array $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $oldData = [
            'name' => $purchasePlan->name,
            'year' => $purchasePlan->year
        ];

        // Actualizar datos básicos del plan
        $purchasePlan->name = $data['name'];
        $purchasePlan->year = $data['year'];
        $purchasePlan->updated_by = auth()->id();

        // Si se proporciona un nuevo archivo, crear/actualizar FormF1
        if (isset($data['file'])) {
            $formF1 = $this->createFormF1($data);
            $purchasePlan->form_f1_id = $formF1->id;
        } elseif (isset($data['amount']) && $purchasePlan->formF1) {
            // Solo actualizar el monto si no hay archivo nuevo
            $purchasePlan->formF1->amount = $data['amount'];
            $purchasePlan->formF1->updated_by = auth()->id();
            $purchasePlan->formF1->save();
        }

        $purchasePlan->save();

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'update',
            'Plan de compra actualizado',
            [
                'old_data' => $oldData,
                'new_data' => [
                    'name' => $purchasePlan->name,
                    'year' => $purchasePlan->year
                ]
            ]
        );

        return $purchasePlan;
    }

    /**
     * Elimina un plan de compra
     */
    public function deletePurchasePlan($id)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $planData = [
            'name' => $purchasePlan->name,
            'year' => $purchasePlan->year,
            'direction' => $purchasePlan->direction->name ?? 'N/A'
        ];

        $purchasePlan->delete();

        // Registrar en el historial (antes de eliminar)
        HistoryPurchaseHistory::logAction(
            $id,
            'delete',
            'Plan de compra eliminado',
            $planData
        );
    }

    /**
     * Actualiza el estado de un plan de compra
     */
    public function sendPurchasePlan($purchasePlan, $statusId, $request = []) 
    {       
        $oldStatusId = $purchasePlan->getCurrentStatusId();
        $oldStatusName = $purchasePlan->getCurrentStatusName();
        
        $this->createPurchasePlanStatus($purchasePlan->id, $statusId, $request);

        $newStatus = \App\Models\StatusPurchasePlan::find($statusId);
        
        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'status_change',
            "Estado cambiado de '{$oldStatusName}' a '{$newStatus->name}'",
            [
                'old_status' => [
                    'id' => $oldStatusId,
                    'name' => $oldStatusName
                ],
                'new_status' => [
                    'id' => $statusId,
                    'name' => $newStatus->name
                ],
                'request_data' => $request
            ]
        );
    }

    /**
     * Actualiza el estado de un plan de compra por ID
     */
    public function updatePurchasePlanStatus($id, $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $oldStatusId = $purchasePlan->getCurrentStatusId();
        $oldStatusName = $purchasePlan->getCurrentStatusName();
        
        $this->createPurchasePlanStatus($purchasePlan->id, $data['status_purchase_plan_id'], $data);

        $newStatus = \App\Models\StatusPurchasePlan::find($data['status_purchase_plan_id']);

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'status_change',
            "Estado actualizado de '{$oldStatusName}' a '{$newStatus->name}'",
            [
                'old_status' => [
                    'id' => $oldStatusId,
                    'name' => $oldStatusName
                ],
                'new_status' => [
                    'id' => $data['status_purchase_plan_id'],
                    'name' => $newStatus->name
                ],
                'comment' => $data['sending_comment'] ?? null
            ]
        );

        return $purchasePlan;
    }

    /**
     * Sube y asocia un archivo de decreto al plan de compra
     */
    public function uploadFileDecreto(array $data)
    {
        $file = $this->createFile($data);

        $purchasePlan = $this->getPurchasePlanByToken($data['token_purchase_plan']);
        $purchasePlan->decreto_id = $file->id;
        $purchasePlan->save();

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'file_upload',
            'Archivo de decreto subido',
            [
                'file_name' => $file->name,
                'file_size' => $file->size,
                'file_type' => $file->type,
                'file_url' => $file->url
            ]
        );

        return $purchasePlan;
    }

    /**
     * Sube y asocia un archivo Form F1 al plan de compra
     */
    public function uploadFileFormF1(array $data)
    {
        $formF1 = $this->createFormF1($data);

        $purchasePlan = $this->getPurchasePlanByToken($data['token_purchase_plan']);
        $purchasePlan->form_f1_id = $formF1->id;
        $purchasePlan->save();

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'file_upload',
            'Archivo Form F1 subido',
            [
                'file_name' => $formF1->name,
                'file_size' => $formF1->size,
                'file_type' => $formF1->type,
                'file_url' => $formF1->url,
                'amount' => $formF1->amount
            ]
        );

        return $purchasePlan;
    }

    /**
     * Crea un nuevo registro de estado para un plan de compra
     */
    private function createPurchasePlanStatus($purchasePlanId, $statusId, $data = [])
    {
        $purchasePlanStatus = new PurchasePlanStatus();
        $purchasePlanStatus->purchase_plan_id = $purchasePlanId;
        $purchasePlanStatus->status_purchase_plan_id = $statusId;
        $purchasePlanStatus->sending_date = now();
        $purchasePlanStatus->sending_comment = $data['sending_comment'] ?? null;
        $purchasePlanStatus->created_by = auth()->id();
        $purchasePlanStatus->save();

        return $purchasePlanStatus;
    }

    /**
     * Crea un nuevo registro de FormF1 en la base de datos
     */
    private function createFormF1(array $data): FormF1
    {
        $direction = auth()->user()->direction;
        $currentDate = now()->format('Y-m-d H:i');
        $nameFile = $data['name_file'] ?? "{$currentDate} - {$direction->name} - FormF1";

        $formF1 = new FormF1();
        $formF1->name = $nameFile;
        $formF1->description = $data['description_file'] ?? "Formulario F1 generado automáticamente para el plan de compra de la dirección {$direction->name}";
        $formF1->amount = $data['amount'] ?? 0;
        $formF1->created_by = auth()->id();

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            $formF1->size = $data['file']->getSize();
            $formF1->type = $data['file']->getClientMimeType();

            // Generar nombre único para el archivo
            $fileName = Str::slug($nameFile) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            // Almacenar archivo en el disco público
            $filePath = $data['file']->storeAs('uploads/form_f1', $fileName, 'public');
            // Generar URL pública del archivo
            $formF1->url = url('storage/' . $filePath);
        }

        $formF1->save();
        return $formF1;
    }

    /**
     * Crea un nuevo registro de archivo en la base de datos
     */
    private function createFile(array $data): File
    {
        $direction = auth()->user()->direction;
        $currentDate = now()->format('Y-m-d H:i');
        $nameFile = $data['name_file'] ?? "{$currentDate} - {$direction->name}";

        $file = new File();
        $file->name = $nameFile;
        $file->description = $data['description_file'] ?? "Archivo generado automáticamente para el plan de compra de la dirección {$direction->name}";
        $file->size = $data['file']->getSize();
        $file->type = $data['file']->getClientMimeType();
        $file->extension = $data['file']->getClientOriginalExtension();
        $file->created_by = auth()->id();

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            // Generar nombre único para el archivo
            $fileName = Str::slug($nameFile) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            // Almacenar archivo en el disco público
            $filePath = $data['file']->storeAs('uploads', $fileName, 'public');
            // Generar URL pública del archivo
            $file->url = url('storage/' . $filePath);
        }

        $file->save();
        return $file;
    }
}
