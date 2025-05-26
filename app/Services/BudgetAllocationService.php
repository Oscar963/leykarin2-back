<?php

namespace App\Services;

use App\Models\BudgetAllocation;
use Illuminate\Support\Str;

class BudgetAllocationService
{
    /**
     * Obtiene todas las asignaciones presupuestarias
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBudgetAllocations()
    {
        return BudgetAllocation::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene asignaciones presupuestarias paginadas con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllBudgetAllocationsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = BudgetAllocation::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('description', 'LIKE', "%{$query}%")
                    ->orWhere('code', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene una asignación presupuestaria por su ID
     *
     * @param int $id ID de la asignación presupuestaria
     * @return BudgetAllocation
     */
    public function getBudgetAllocationById($id)
    {
        return BudgetAllocation::findOrFail($id);
    }

    /**
     * Crea una nueva asignación presupuestaria
     *
     * @param array $data Datos de la asignación presupuestaria
     * @return BudgetAllocation
     */
    public function createBudgetAllocation(array $data)
    {
        $budgetAllocation = new BudgetAllocation();
        $budgetAllocation->description = trim($data['description']);
        $budgetAllocation->code = trim($data['code']);
        $budgetAllocation->cod_budget_allocation_type = trim($data['cod_budget_allocation_type']);
        $budgetAllocation->save();

        return $budgetAllocation;
    }

    /**
     * Actualiza una asignación presupuestaria existente
     *
     * @param int $id ID de la asignación presupuestaria
     * @param array $data Datos actualizados
     * @return BudgetAllocation
     */
    public function updateBudgetAllocation($id, array $data)
    {
        $budgetAllocation = $this->getBudgetAllocationById($id);
        $budgetAllocation->description = trim($data['description']);
        $budgetAllocation->code = trim($data['code']);
        $budgetAllocation->save();

        return $budgetAllocation;
    }

    /**
     * Elimina una asignación presupuestaria
     *
     * @param int $id ID de la asignación presupuestaria
     * @return void
     */
    public function deleteBudgetAllocation($id)
    {
        $budgetAllocation = $this->getBudgetAllocationById($id);
        $budgetAllocation->delete();
    }
}
