<?php

namespace App\Services;

use App\Models\Modification;
use App\Models\ModificationHistory;
use Illuminate\Support\Facades\DB;

class ModificationService
{
    /**
     * Obtiene todas las modificaciones con paginación y filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllModificationsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Modification::with([
            'purchasePlan.direction',
            'createdBy',
            'updatedBy'
        ])->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('reason', 'LIKE', "%{$query}%")
                    ->orWhere('modification_number', 'LIKE', "%{$query}%")
                    ->orWhereHas('purchasePlan', function ($purchasePlanQuery) use ($query) {
                        $purchasePlanQuery->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene modificaciones por plan de compra
     *
     * @param int $purchasePlanId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getModificationsByPurchasePlan(int $purchasePlanId)
    {
        return Modification::with([
            'createdBy',
            'updatedBy',
            'history.user'
        ])
        ->where('purchase_plan_id', $purchasePlanId)
        ->orderBy('modification_number', 'asc')
        ->get();
    }

    /**
     * Obtiene una modificación por su ID
     *
     * @param int $id
     * @return Modification
     */
    public function getModificationById(int $id)
    {
        return Modification::with([
            'purchasePlan.direction',
            'createdBy',
            'updatedBy',
            'history.user'
        ])->findOrFail($id);
    }

    /**
     * Crea una nueva modificación
     *
     * @param array $data
     * @return Modification
     */
    public function createModification(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Obtener el siguiente número de modificación
            $data['modification_number'] = Modification::getNextModificationNumber($data['purchase_plan_id']);
            $data['created_by'] = auth()->id();
            
            $modification = Modification::create($data);
            
            // Registrar en el historial
            $this->logModificationAction(
                $modification->id,
                ModificationHistory::ACTION_CREATE,
                'Modificación creada',
                [
                    'modification_number' => $modification->modification_number,
                    'reason' => $modification->reason,
                    'status' => $modification->status
                ]
            );
            
            DB::commit();
            return $modification;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una modificación existente
     *
     * @param int $id
     * @param array $data
     * @return Modification
     */
    public function updateModification(int $id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $modification = $this->getModificationById($id);
            $oldData = $modification->toArray();
            
            $data['updated_by'] = auth()->id();
            $modification->update($data);
            
            // Registrar en el historial
            $this->logModificationAction(
                $modification->id,
                ModificationHistory::ACTION_UPDATE,
                'Modificación actualizada',
                [
                    'old_data' => $oldData,
                    'new_data' => $modification->toArray()
                ]
            );
            
            DB::commit();
            return $modification;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cambia el estado de una modificación
     *
     * @param int $id
     * @param string $newStatus
     * @param string|null $comment
     * @return Modification
     */
    public function changeModificationStatus(int $id, string $newStatus, ?string $comment = null)
    {
        DB::beginTransaction();
        
        try {
            $modification = $this->getModificationById($id);
            $oldStatus = $modification->status;
            
            $modification->update([
                'status' => $newStatus,
                'updated_by' => auth()->id()
            ]);
            
            // Registrar en el historial
            $this->logModificationAction(
                $modification->id,
                ModificationHistory::ACTION_STATUS_CHANGE,
                "Estado cambiado de '{$oldStatus}' a '{$newStatus}'" . ($comment ? " - {$comment}" : ''),
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'comment' => $comment
                ]
            );
            
            DB::commit();
            return $modification;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una modificación
     *
     * @param int $id
     * @return void
     */
    public function deleteModification(int $id)
    {
        DB::beginTransaction();
        
        try {
            $modification = $this->getModificationById($id);
            
            // Registrar en el historial antes de eliminar
            $this->logModificationAction(
                $modification->id,
                ModificationHistory::ACTION_DELETE,
                'Modificación eliminada',
                [
                    'modification_number' => $modification->modification_number,
                    'reason' => $modification->reason,
                    'status' => $modification->status
                ]
            );
            
            $modification->delete();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Registra una acción en el historial de modificaciones
     *
     * @param int $modificationId
     * @param string $action
     * @param string $description
     * @param array|null $details
     * @return ModificationHistory
     */
    private function logModificationAction(int $modificationId, string $action, string $description, ?array $details = null)
    {
        return ModificationHistory::create([
            'modification_id' => $modificationId,
            'action' => $action,
            'description' => $description,
            'details' => $details,
            'user_id' => auth()->id(),
            'date' => now()
        ]);
    }

    /**
     * Obtiene estadísticas de modificaciones por plan de compra
     *
     * @param int $purchasePlanId
     * @return array
     */
    public function getModificationStats(int $purchasePlanId): array
    {
        $modifications = Modification::where('purchase_plan_id', $purchasePlanId)->get();
        
        return [
            'total' => $modifications->count(),
            'active' => $modifications->where('status', Modification::STATUS_ACTIVE)->count(),
            'pending' => $modifications->where('status', Modification::STATUS_PENDING)->count(),
            'approved' => $modifications->where('status', Modification::STATUS_APPROVED)->count(),
            'rejected' => $modifications->where('status', Modification::STATUS_REJECTED)->count(),
            'inactive' => $modifications->where('status', Modification::STATUS_INACTIVE)->count(),
        ];
    }
} 