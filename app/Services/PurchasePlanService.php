<?php

namespace App\Services;

use App\Models\File;
use App\Models\PurchasePlan;
use Illuminate\Support\Str;

class PurchasePlanService
{
    /**
     * Roles que tienen acceso completo a todos los planes de compra
     */
    private const ADMIN_ROLES = [
        'Administrador del Sistema',
        'Alcalde',
        'Administrador Municipal'
    ];

    /**
     * Estado por defecto para nuevos planes de compra
     */
    private const DEFAULT_STATUS_ID = 1; // Borrador

    /**
     * Obtiene todos los planes de compra con paginación y filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPurchasePlansByQuery(?string $query, int $perPage = 15)
    {
        $user = auth()->user();
        $directionId = $user->direction ? $user->direction->id : null;

        $queryBuilder = PurchasePlan::orderBy('created_at', 'DESC')
            ->when($directionId && !$this->canUserViewAllPlans($user), function ($q) use ($directionId) {
                $q->where('direction_id', $directionId);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un plan de compra por su ID
     *
     * @param int $id ID del plan de compra
     * @return PurchasePlan
     */
    public function getPurchasePlanById($id)
    {
        return PurchasePlan::findOrFail($id);
    }

    /**
     * Obtiene un plan de compra por su token
     *
     * @param string $token Token del plan de compra
     * @return PurchasePlan|null
     */
    public function getPurchasePlanByToken($token)
    {
        return PurchasePlan::where('token', $token)->first();
    }

    /**
     * Crea un nuevo plan de compra
     *
     * @param array $data Datos del plan de compra
     * @return PurchasePlan
     */
    public function createPurchasePlan(array $data)
    {
        $direction = auth()->user()->direction;
        $file = $this->createFile($data);

        $purchasePlan = new PurchasePlan();
        $purchasePlan->name = $data['name'];
        $purchasePlan->date_created = now();
        $purchasePlan->token = Str::random(32);
        $purchasePlan->year = $data['year'];
        $purchasePlan->amount_F1 = $data['amount_F1'];
        $purchasePlan->form_F1_id = $file->id;
        $purchasePlan->status_purchase_plan_id = self::DEFAULT_STATUS_ID;
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Actualiza un plan de compra existente
     *
     * @param int $id ID del plan de compra
     * @param array $data Datos actualizados
     * @return PurchasePlan
     */
    public function updatePurchasePlan($id, array $data)
    {
        if (isset($data['file'])) {
            $file = $this->createFile($data);
        }

        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->name = $data['name'];
        $purchasePlan->year = $data['year'];
        $purchasePlan->amount_F1 = $data['amount_F1'];
        $purchasePlan->form_F1_id = $file->id ?? $purchasePlan->form_F1_id; 
        $purchasePlan->updated_by = auth()->id();
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Elimina un plan de compra
     *
     * @param int $id ID del plan de compra
     * @return void
     */
    public function deletePurchasePlan($id)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->delete();
    }

    /**
     * Sube y asocia un archivo de decreto al plan de compra
     *
     * @param array $data Datos del archivo
     * @return PurchasePlan
     */
    public function uploadFileDecreto(array $data)
    {
        $file = $this->createFile($data);

        $purchasePlan = $this->getPurchasePlanById($data['purchase_plan_id']);
        $purchasePlan->decreto_id = $file->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Sube y asocia un archivo Form F1 al plan de compra
     *
     * @param array $data Datos del archivo
     * @return PurchasePlan
     */
    public function uploadFileFormF1(array $data)
    {
        $file = $this->createFile($data);

        $purchasePlan = $this->getPurchasePlanById($data['purchase_plan_id']);
        $purchasePlan->form_F1_id = $file->id;
        $purchasePlan->amount_F1 = $data['amount_F1'];
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Crea un nuevo registro de archivo
     *
     * @param array $data Datos del archivo
     * @return File
     */
    private function createFile(array $data): File
    {
        $file = new File();
        $file->name = $data['name_file'];
        $file->description = $data['description_file'];
        $file->size = $data['file']->getSize();
        $file->type = $data['file']->getClientMimeType();
        $file->created_by = auth()->id();

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            $fileName = Str::slug($data['name_file']) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            $filePath = $data['file']->storeAs('uploads', $fileName, 'public');
            $file->url = url('storage/' . $filePath);
        }

        $file->save();
        return $file;
    }

    /**
     * Verifica si el usuario puede ver todos los planes de compra
     *
     * @param \App\Models\User $user Usuario a verificar
     * @return bool
     */
    private function canUserViewAllPlans($user): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        return !empty(array_intersect($userRoles, self::ADMIN_ROLES));
    }

    public function sendPurchasePlan($purchasePlan, $statusId)
    {
        /* 1.- Borrador, 2.- Para aprobación, 3.- Aprobado, 4.- Decretado, 5.- Publicado */
        $purchasePlan->status_purchase_plan_id = $statusId;
        $purchasePlan->save();
    }

    public function updatePurchasePlanStatus($id, $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->status_purchase_plan_id = $data['status_purchase_plan_id'];
        $purchasePlan->save();
        return $purchasePlan;
    }   
}
