<?php

namespace App\Services;

use App\Models\File;
use App\Models\FormF1;
use App\Models\PurchasePlan;
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
        $purchasePlan->status_purchase_plan_id = 1; // Borrador
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

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
        $purchasePlan->status_purchase_plan_id = 1; // Borrador
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Actualiza un plan de compra existente
     */
    public function updatePurchasePlan($id, array $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);

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
        return $purchasePlan;
    }

    /**
     * Actualiza el estado de un plan de compra
     */
    public function sendPurchasePlan($purchasePlan, $statusId)
    {
        $purchasePlan->status_purchase_plan_id = $statusId;
        $purchasePlan->save();
    }

    /**
     * Actualiza el estado de un plan de compra por ID
     */
    public function updatePurchasePlanStatus($id, $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->status_purchase_plan_id = $data['status_purchase_plan_id'];
        $purchasePlan->save();
        return $purchasePlan;
    }

    /**
     * Elimina un plan de compra
     */
    public function deletePurchasePlan($id)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->delete();
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

        return $purchasePlan;
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
