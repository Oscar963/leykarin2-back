<?php

namespace App\Services;

use App\Models\Modification;
use App\Models\ModificationType;

class ModificationService
{
    /**
     * Obtiene todas las modificaciones con paginación y filtrado
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
     * Crea una nueva modificación
     */
    public function createModification(array $data)
    {
        $data['created_by'] = auth()->id();
        
        return Modification::create($data);
    }

    /**
     * Actualiza una modificación existente
     */
    public function updateModification(int $id, array $data)
    {
        $modification = $this->getModificationById($id);
        
        $modification->update($data);
        
        return $modification;
    }

    /**
     * Cambia el estado de una modificación
     */
    public function changeModificationStatus(int $id, string $status)
    {
        $modification = $this->getModificationById($id);
        
        $modification->status = $status;
        $modification->save();
        
        return $modification;
    }

    /**
     * Elimina una modificación
     */
    public function deleteModification(int $id)
    {
        $modification = $this->getModificationById($id);
        $modification->delete();
    }

    /**
     * Obtiene modificaciones pendientes de aprobación
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
     */
    public function getAvailableStatuses(): array
    {
        return Modification::getAvailableStatuses();
    }

    /**
     * Obtiene modificaciones por plan de compra
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
     */
    public function getAvailableTypes()
    {
        return ModificationType::select('id', 'name', 'description')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Obtiene estadísticas básicas de modificaciones
     */
    public function getBasicStatistics(): array
    {
        $total = Modification::count();
        $pending = Modification::where('status', Modification::STATUS_PENDING)->count();
        $approved = Modification::where('status', Modification::STATUS_APPROVED)->count();
        $rejected = Modification::where('status', Modification::STATUS_REJECTED)->count();
        $active = Modification::where('status', Modification::STATUS_ACTIVE)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'active' => $active
        ];
    }

    /**
     * Obtiene estadísticas por tipo de modificación
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
     * Obtiene modificaciones por usuario creador
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
     * Busca modificaciones por nombre o descripción
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
                  ->orWhere('version', 'LIKE', "%{$search}%");
        })
        ->orderBy('created_at', 'DESC')
        ->paginate($perPage);
    }
} 