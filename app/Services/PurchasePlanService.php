<?php

namespace App\Services;

use App\Models\File;
use App\Models\FormF1;
use App\Models\PurchasePlan;
use Illuminate\Support\Str;

/**
 * Servicio para gestión de Planes de Compra
 * 
 * Este servicio maneja todas las operaciones relacionadas con la creación,
 * actualización, eliminación y consulta de planes de compra, incluyendo
 * la gestión de archivos asociados y el control de permisos por roles.
 * 
 * @package App\Services
 * @author Sistema Plan de Compras
 * @version 1.0
 */
class PurchasePlanService
{
    // ============================================================================
    // CONSTANTES
    // ============================================================================

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

    // ============================================================================
    // MÉTODOS PÚBLICOS - CONSULTAS
    // ============================================================================

    /**
     * Obtiene todos los planes de compra con paginación y filtrado
     *
     * @param string|null $query Término de búsqueda para filtrar por nombre
     * @param int $perPage Número de elementos por página (por defecto 15)
     * @return \Illuminate\Pagination\LengthAwarePaginator Lista paginada de planes de compra
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

    public function getAllPurchasePlansByYear(int $year)
    {
        $queryBuilder = PurchasePlan::orderBy('created_at', 'DESC')
            ->where('year', $year);

        return $queryBuilder->get();
    }

    /**
     * Obtiene un plan de compra por su ID
     *
     * @param int $id ID del plan de compra
     * @return PurchasePlan Instancia del plan de compra
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el plan no existe
     */
    public function getPurchasePlanById($id)
    {
        return PurchasePlan::findOrFail($id);
    }

    /**
     * Obtiene un plan de compra por su token único
     *
     * @param string $token Token único del plan de compra
     * @return PurchasePlan|null Instancia del plan de compra o null si no existe
     */
    public function getPurchasePlanByToken($token)
    {
        return PurchasePlan::where('token', $token)->first();
    }

    /**
     * Obtiene un plan de compra por su año
     *
     * @param int $year Año del plan de compra
     * @return PurchasePlan|null Instancia del plan de compra o null si no existe
     */
    public function getPurchasePlanByYear($year)
    {
        return PurchasePlan::where('year', $year)->first();
    }

    // ============================================================================
    // MÉTODOS PÚBLICOS - OPERACIONES CRUD
    // ============================================================================

    /**
     * Crea un nuevo plan de compra
     *
     * @param array $data Datos del plan de compra
     *                    - name: string Nombre del plan
     *                    - year: int Año del plan
     *                    - amount: float Monto del formulario F1
     *                    - file: UploadedFile Archivo del formulario F1
     *                    - name_file: string (opcional) Nombre del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return PurchasePlan Instancia del plan de compra creado
     * @throws \Exception Si hay error en la creación del archivo
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
        $purchasePlan->status_purchase_plan_id = self::DEFAULT_STATUS_ID;
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Crea un plan de compra por defecto para un año específico
     *
     * @param int $year Año para el plan de compra
     * @return PurchasePlan Instancia del plan de compra creado
     */
    public function createDefaultPurchasePlan(int $year): PurchasePlan
    {
        $direction = auth()->user()->direction;

        $purchasePlan = new PurchasePlan();
        $purchasePlan->name = "Plan de Compra {$year} - {$direction->name}";
        $purchasePlan->date_created = now();
        $purchasePlan->token = Str::random(32);
        $purchasePlan->year = $year;
        $purchasePlan->status_purchase_plan_id = self::DEFAULT_STATUS_ID;
        $purchasePlan->created_by = auth()->id();
        $purchasePlan->direction_id = $direction->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    /**
     * Actualiza un plan de compra existente
     *
     * @param int $id ID del plan de compra a actualizar
     * @param array $data Datos actualizados
     *                    - name: string Nombre del plan
     *                    - year: int Año del plan
     *                    - amount: float (opcional) Monto del formulario F1
     *                    - file: UploadedFile (opcional) Nuevo archivo del formulario F1
     *                    - name_file: string (opcional) Nombre del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return PurchasePlan Instancia del plan de compra actualizado
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el plan no existe
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
     * Elimina un plan de compra
     *
     * @param int $id ID del plan de compra a eliminar
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el plan no existe
     */
    public function deletePurchasePlan($id)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->delete();
    }

    // ============================================================================
    // MÉTODOS PÚBLICOS - GESTIÓN DE ESTADO Y ARCHIVOS
    // ============================================================================

    /**
     * Actualiza el estado de un plan de compra
     *
     * @param PurchasePlan $purchasePlan Instancia del plan de compra
     * @param int $statusId ID del nuevo estado
     *                      1: Borrador, 2: Para aprobación, 3: Aprobado, 4: Decretado, 5: Publicado
     * @return void
     */
    public function sendPurchasePlan($purchasePlan, $statusId)
    {
        $purchasePlan->status_purchase_plan_id = $statusId;
        $purchasePlan->save();
    }

    /**
     * Actualiza el estado de un plan de compra por ID
     *
     * @param int $id ID del plan de compra
     * @param array $data Datos con el nuevo estado
     *                    - status_purchase_plan_id: int ID del estado
     * @return PurchasePlan Instancia del plan de compra actualizado
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el plan no existe
     */
    public function updatePurchasePlanStatus($id, $data)
    {
        $purchasePlan = $this->getPurchasePlanById($id);
        $purchasePlan->status_purchase_plan_id = $data['status_purchase_plan_id'];
        $purchasePlan->save();
        return $purchasePlan;
    }

    /**
     * Sube y asocia un archivo de decreto al plan de compra
     *
     * @param array $data Datos del archivo
     *                    - token_purchase_plan: string Token del plan de compra
     *                    - file: UploadedFile Archivo del decreto
     *                    - name_file: string (opcional) Nombre personalizado del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return PurchasePlan Instancia del plan de compra actualizado
     * @throws \Exception Si hay error en la creación del archivo
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
     *
     * @param array $data Datos del archivo
     *                    - token_purchase_plan: string Token del plan de compra
     *                    - file: UploadedFile Archivo del formulario F1
     *                    - amount: float Monto del formulario F1
     *                    - name_file: string (opcional) Nombre personalizado del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return PurchasePlan Instancia del plan de compra actualizado
     * @throws \Exception Si hay error en la creación del archivo
     */
    public function uploadFileFormF1(array $data)
    {
        $formF1 = $this->createFormF1($data);

        $purchasePlan = $this->getPurchasePlanByToken($data['token_purchase_plan']);
        $purchasePlan->form_f1_id = $formF1->id;
        $purchasePlan->save();

        return $purchasePlan;
    }

    // ============================================================================
    // MÉTODOS PRIVADOS - UTILIDADES
    // ============================================================================

    /**
     * Crea un nuevo registro de FormF1 en la base de datos y almacena el archivo físico
     *
     * @param array $data Datos del archivo FormF1
     *                    - file: UploadedFile Archivo del formulario F1
     *                    - amount: float Monto del formulario F1
     *                    - name_file: string (opcional) Nombre personalizado del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return FormF1 Instancia del FormF1 creado
     * @throws \Exception Si hay error al guardar el archivo
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
     * Crea un nuevo registro de archivo en la base de datos y almacena el archivo físico
     *
     * @param array $data Datos del archivo
     *                    - file: UploadedFile Archivo a subir
     *                    - name_file: string (opcional) Nombre personalizado del archivo
     *                    - description_file: string (opcional) Descripción del archivo
     * @return File Instancia del archivo creado
     * @throws \Exception Si hay error al guardar el archivo
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

    /**
     * Verifica si el usuario actual puede ver todos los planes de compra
     * 
     * Los usuarios con roles administrativos pueden ver todos los planes,
     * mientras que otros usuarios solo pueden ver los de su dirección.
     *
     * @param \App\Models\User $user Usuario a verificar
     * @return bool True si puede ver todos los planes, false si solo los de su dirección
     */
    private function canUserViewAllPlans($user): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        return !empty(array_intersect($userRoles, self::ADMIN_ROLES));
    }
}
