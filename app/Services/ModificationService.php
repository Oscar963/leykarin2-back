<?php

namespace App\Services;

use App\Models\Modification;
use App\Models\ModificationType;
use App\Models\PurchasePlan;
use App\Models\User;
use App\Mail\ModificationCreated;
use App\Jobs\SendModificationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ModificationService
{
    /**
     * Obtiene todas las modificaciones con paginación y filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @param string|null $status Filtro por estado
     * @param int|null $modificationTypeId Filtro por tipo de modificación
     * @param string|null $startDate Fecha de inicio para filtro
     * @param string|null $endDate Fecha de fin para filtro
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllModificationsByQuery(?string $query, int $perPage = 15, ?string $status = null, ?int $modificationTypeId = null, ?string $startDate = null, ?string $endDate = null)
    {
        $queryBuilder = Modification::with([
            'modificationType',
            'purchasePlan.direction',
            'createdBy'
        ])->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('version', 'LIKE', "%{$query}%")
                    ->orWhereHas('modificationType', function ($typeQuery) use ($query) {
                        $typeQuery->where('name', 'LIKE', "%{$query}%");
                    })
                    ->orWhereHas('purchasePlan', function ($planQuery) use ($query) {
                        $planQuery->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        if ($status) {
            $queryBuilder->where('status', $status);
        }

        if ($modificationTypeId) {
            $queryBuilder->where('modification_type_id', $modificationTypeId);
        }

        if ($startDate && $endDate) {
            $queryBuilder->whereBetween('date', [$startDate, $endDate]);
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene una modificación por su ID
     *
     * @param int $id ID de la modificación
     * @return Modification
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getModificationById(int $id)
    {
        return Modification::with([
            'modificationType',
            'purchasePlan.direction',
            'createdBy'
        ])->findOrFail($id);
    }

    /**
     * Obtiene una modificación por su token único
     *
     * @param string $token Token de la modificación
     * @return Modification
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getModificationByToken(string $token)
    {
        return Modification::with([
            'modificationType',
            'purchasePlan.direction',
            'createdBy'
        ])->where('token', $token)->firstOrFail();
    }

    /**
     * Crea una nueva modificación
     *
     * @param array $data Datos de la modificación
     * @return Modification
     * @throws Exception
     */
    public function createModification(array $data)
    {
        try {
            // Verificar que el plan de compra existe
            $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
            
            // Verificar que el tipo de modificación existe
            $modificationType = ModificationType::findOrFail($data['modification_type_id']);

            $modification = new Modification();
            $modification->name = trim($data['name']);
            $modification->description = trim($data['description']);
            
            // Generar versión automáticamente de forma correlativa
            $modification->version = $this->generateNextVersion($data['modification_type_id'], $data['purchase_plan_id']);
            
            // Establecer fecha automáticamente (hoy)
            $modification->date = now()->format('Y-m-d');
            
            $modification->status = $data['status'] ?? Modification::STATUS_PENDING;
            $modification->modification_type_id = $modificationType->id;
            $modification->purchase_plan_id = $purchasePlan->id;
            $modification->created_by = auth()->id();
            $modification->token = Str::random(32); // Token único para la modificación
            $modification->save();

            // Enviar correo al visador
            $this->sendModificationNotification($modification, $data['email_content'] ?? null);

            return $modification->load(['modificationType', 'purchasePlan.direction', 'createdBy']);
        } catch (Exception $e) {
            throw new Exception('Error al crear la modificación: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una modificación existente
     *
     * @param int $id ID de la modificación
     * @param array $data Datos actualizados
     * @return Modification
     * @throws Exception
     */
    public function updateModification(int $id, array $data)
    {
        try {
            $modification = $this->getModificationById($id);
            
            // Verificar que el tipo de modificación existe si se está actualizando
            if (isset($data['modification_type_id'])) {
                $modificationType = ModificationType::findOrFail($data['modification_type_id']);
                $modification->modification_type_id = $modificationType->id;
            }

            // Verificar que el plan de compra existe si se está actualizando
            if (isset($data['purchase_plan_id'])) {
                $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
                $modification->purchase_plan_id = $purchasePlan->id;
            }

            // Actualizar campos básicos
            if (isset($data['name'])) {
                $modification->name = trim($data['name']);
            }
            if (isset($data['description'])) {
                $modification->description = trim($data['description']);
            }
            
            // Si se está cambiando el tipo de modificación o plan de compra, generar nueva versión
            if (isset($data['modification_type_id']) || isset($data['purchase_plan_id'])) {
                $newTypeId = $data['modification_type_id'] ?? $modification->modification_type_id;
                $newPlanId = $data['purchase_plan_id'] ?? $modification->purchase_plan_id;
                $modification->version = $this->generateNextVersion($newTypeId, $newPlanId);
            }
            
            if (isset($data['status'])) {
                $modification->status = $data['status'];
            }

            $modification->updated_by = auth()->id();
            $modification->save();

            return $modification->load(['modificationType', 'purchasePlan.direction', 'createdBy']);
        } catch (Exception $e) {
            throw new Exception('Error al actualizar la modificación: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una modificación existente por su token
     *
     * @param string $token Token de la modificación
     * @param array $data Datos actualizados
     * @return Modification
     * @throws Exception
     */
    public function updateModificationByToken(string $token, array $data)
    {
        try {
            $modification = $this->getModificationByToken($token);
            
            // Verificar que el tipo de modificación existe si se está actualizando
            if (isset($data['modification_type_id'])) {
                $modificationType = ModificationType::findOrFail($data['modification_type_id']);
                $modification->modification_type_id = $modificationType->id;
            }

            // Verificar que el plan de compra existe si se está actualizando
            if (isset($data['purchase_plan_id'])) {
                $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
                $modification->purchase_plan_id = $purchasePlan->id;
            }

            // Actualizar campos básicos
            if (isset($data['name'])) {
                $modification->name = trim($data['name']);
            }
            if (isset($data['description'])) {
                $modification->description = trim($data['description']);
            }
            
            // Si se está cambiando el tipo de modificación o plan de compra, generar nueva versión
            if (isset($data['modification_type_id']) || isset($data['purchase_plan_id'])) {
                $newTypeId = $data['modification_type_id'] ?? $modification->modification_type_id;
                $newPlanId = $data['purchase_plan_id'] ?? $modification->purchase_plan_id;
                $modification->version = $this->generateNextVersion($newTypeId, $newPlanId);
            }
            
            if (isset($data['status'])) {
                $modification->status = $data['status'];
            }

            $modification->updated_by = auth()->id();
            $modification->save();

            return $modification->load(['modificationType', 'purchasePlan.direction', 'createdBy']);
        } catch (Exception $e) {
            throw new Exception('Error al actualizar la modificación: ' . $e->getMessage());
        }
    }

    /**
     * Cambia el estado de una modificación
     *
     * @param int $id ID de la modificación
     * @param string $status Nuevo estado
     * @return Modification
     * @throws Exception
     */
    public function changeModificationStatus(int $id, string $status)
    {
        try {
            $modification = $this->getModificationById($id);
            
            // Validar que el estado sea válido
            $validStatuses = Modification::getAvailableStatuses();
            if (!array_key_exists($status, $validStatuses)) {
                throw new Exception('Estado no válido. Estados permitidos: ' . implode(', ', array_keys($validStatuses)));
            }

            $modification->status = $status;
            $modification->updated_by = auth()->id();
            $modification->save();

            return $modification->load(['modificationType', 'purchasePlan.direction', 'createdBy']);
        } catch (Exception $e) {
            throw new Exception('Error al cambiar el estado de la modificación: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una modificación
     *
     * @param int $id ID de la modificación
     * @return void
     * @throws Exception
     */
    public function deleteModification(int $id)
    {
        try {
            $modification = $this->getModificationById($id);
            
            // Verificar si la modificación puede ser eliminada
            if ($modification->status === Modification::STATUS_APPROVED) {
                throw new Exception('No se puede eliminar una modificación que ya ha sido aprobada');
            }

            $modification->delete();
        } catch (Exception $e) {
            throw new Exception('Error al eliminar la modificación: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene modificaciones pendientes de aprobación
     *
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPendingApprovalModifications(int $perPage = 15)
    {
        return Modification::with([
            'modificationType',
            'purchasePlan.direction',
            'createdBy'
        ])
        ->where('status', Modification::STATUS_PENDING)
        ->orderBy('created_at', 'DESC')
        ->paginate($perPage);
    }

    /**
     * Obtiene los estados disponibles
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return Modification::getAvailableStatuses();
    }

    /**
     * Obtiene modificaciones por plan de compra
     *
     * @param int $purchasePlanId ID del plan de compra
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getModificationsByPurchasePlan(int $purchasePlanId, ?string $query = null, int $perPage = 15)
    {
        $queryBuilder = Modification::with([
            'modificationType',
            'createdBy'
        ])
        ->where('purchase_plan_id', $purchasePlanId)
        ->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('version', 'LIKE', "%{$query}%")
                    ->orWhereHas('modificationType', function ($typeQuery) use ($query) {
                        $typeQuery->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene los tipos de modificación disponibles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTypes()
    {
        return ModificationType::select('id', 'name', 'description')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Obtiene estadísticas básicas de modificaciones
     *
     * @return array
     */
    public function getBasicStatistics(): array
    {
        $total = Modification::count();
        $pending = Modification::where('status', Modification::STATUS_PENDING)->count();
        $approved = Modification::where('status', Modification::STATUS_APPROVED)->count();
        $rejected = Modification::where('status', Modification::STATUS_REJECTED)->count();
        $active = Modification::where('status', Modification::STATUS_ACTIVE)->count();
        $inactive = Modification::where('status', Modification::STATUS_INACTIVE)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'active' => $active,
            'inactive' => $inactive
        ];
    }

    /**
     * Obtiene estadísticas por tipo de modificación
     *
     * @return array
     */
    public function getStatisticsByType(): array
    {
        return Modification::selectRaw('modification_type_id, COUNT(*) as count')
            ->with('modificationType:id,name')
            ->groupBy('modification_type_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->modificationType->name ?? 'Sin tipo' => $item->count];
            })
            ->toArray();
    }

    /**
     * Obtiene estadísticas por plan de compra
     *
     * @param int|null $purchasePlanId ID del plan de compra (opcional)
     * @return array
     */
    public function getStatisticsByPurchasePlan(?int $purchasePlanId = null): array
    {
        $query = Modification::selectRaw('purchase_plan_id, COUNT(*) as count')
            ->with('purchasePlan:id,name,direction_id')
            ->with('purchasePlan.direction:id,name')
            ->groupBy('purchase_plan_id');

        if ($purchasePlanId) {
            $query->where('purchase_plan_id', $purchasePlanId);
        }

        return $query->get()
            ->mapWithKeys(function ($item) {
                $planName = $item->purchasePlan->name ?? 'Plan no encontrado';
                $directionName = $item->purchasePlan->direction->name ?? 'Dirección no encontrada';
                return ["{$directionName} - {$planName}" => $item->count];
            })
            ->toArray();
    }

    /**
     * Obtiene modificaciones por usuario creador
     *
     * @param int $userId ID del usuario
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getModificationsByCreator(int $userId, int $perPage = 15)
    {
        return Modification::with([
            'modificationType',
            'purchasePlan.direction'
        ])
        ->where('created_by', $userId)
        ->orderBy('created_at', 'DESC')
        ->paginate($perPage);
    }

    /**
     * Busca modificaciones por texto
     *
     * @param string $search Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchModifications(string $search, int $perPage = 15)
    {
        return Modification::with([
            'modificationType',
            'purchasePlan.direction',
            'createdBy'
        ])
        ->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('version', 'LIKE', "%{$search}%")
                  ->orWhereHas('modificationType', function ($typeQuery) use ($search) {
                      $typeQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('purchasePlan', function ($planQuery) use ($search) {
                      $planQuery->where('name', 'LIKE', "%{$search}%");
                  });
        })
        ->orderBy('created_at', 'DESC')
        ->paginate($perPage);
    }

    /**
     * Obtiene estadísticas de modificaciones por usuario
     *
     * @param int|null $userId ID del usuario (opcional, si no se proporciona usa el usuario autenticado)
     * @return array
     */
    public function getStatisticsByUser(?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();

        $total = Modification::where('created_by', $userId)->count();
        $pending = Modification::where('created_by', $userId)
            ->where('status', Modification::STATUS_PENDING)->count();
        $approved = Modification::where('created_by', $userId)
            ->where('status', Modification::STATUS_APPROVED)->count();
        $rejected = Modification::where('created_by', $userId)
            ->where('status', Modification::STATUS_REJECTED)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected
        ];
    }

    /**
     * Verifica si una modificación puede ser editada
     *
     * @param int $id ID de la modificación
     * @return bool
     */
    public function canBeEdited(int $id): bool
    {
        try {
            $modification = $this->getModificationById($id);
            return in_array($modification->status, [
                Modification::STATUS_PENDING,
                Modification::STATUS_INACTIVE
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Genera la siguiente versión correlativa para una modificación
     *
     * @param int $modificationTypeId ID del tipo de modificación
     * @param int $purchasePlanId ID del plan de compra
     * @return string
     */
    private function generateNextVersion(int $modificationTypeId, int $purchasePlanId): string
    {
        // Obtener la última modificación del mismo tipo y plan de compra
        $lastModification = Modification::where('modification_type_id', $modificationTypeId)
            ->where('purchase_plan_id', $purchasePlanId)
            ->orderBy('version', 'DESC')
            ->first();

        if (!$lastModification) {
            // Si no hay modificaciones previas, empezar con versión 1.0
            return '1.0';
        }

        // Extraer el número de versión actual
        $currentVersion = $lastModification->version;
        
        // Intentar parsear la versión (formato esperado: X.Y)
        if (preg_match('/^(\d+)\.(\d+)$/', $currentVersion, $matches)) {
            $major = (int)$matches[1];
            $minor = (int)$matches[2];
            
            // Incrementar la versión menor
            $minor++;
            
            // Si la versión menor llega a 10, incrementar la mayor y resetear la menor
            if ($minor >= 10) {
                $major++;
                $minor = 0;
            }
            
            return $major . '.' . $minor;
        }
        
        // Si el formato no es válido, empezar con 1.0
        return '1.0';
    }

    /**
     * Envía notificación por correo al visador sobre una nueva modificación
     *
     * @param Modification $modification
     * @param string|null $emailContent Contenido adicional del correo
     * @return void
     */
    private function sendModificationNotification(Modification $modification, ?string $emailContent = null): void
    {
        try {
            // Correo del visador para testing (en producción esto vendría de configuración o base de datos)
            $visadorEmail = 'oscar.apata@municipalidadarica.cl';
            
            // Enviar correo usando job para procesamiento asíncrono
            SendModificationNotification::dispatch($modification, $visadorEmail, $emailContent);
            
        } catch (Exception $e) {
            // Log del error pero no fallar la creación de la modificación
            Log::error('Error enviando correo de notificación de modificación: ' . $e->getMessage(), [
                'modification_id' => $modification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verifica si una modificación puede ser eliminada
     *
     * @param int $id ID de la modificación
     * @return bool
     */
    public function canBeDeleted(int $id): bool
    {
        try {
            $modification = $this->getModificationById($id);
            return $modification->status !== Modification::STATUS_APPROVED;
        } catch (Exception $e) {
            return false;
        }
    }
} 