<?php

namespace App\Services;

use App\Models\ContractualStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContractualStatusService
{
    /**
     * Obtiene todos los estados contractuales ordenados por fecha de creación (descendente).
     *
     * @return Collection<ContractualStatus>
     */
    public function getAllContractualStatuses(): Collection
    {
        return ContractualStatus::latest()->get();
    }

    /**
     * Obtiene todos los estados contractuales con filtros y paginación.
     *
     * @param string|null $query
     * @param int|null $perPage
     * @return LengthAwarePaginator<ContractualStatus>
     */
    public function getAllContractualStatusesByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return ContractualStatus::latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo estado contractual usando asignación masiva.
     *
     * @param array $data
     * @return ContractualStatus
     */
    public function createContractualStatus(array $data): ContractualStatus
    {
        return ContractualStatus::create($data);
    }

    /**
     * Obtiene un estado contractual por su ID.
     *
     * @param int $id
     * @return ContractualStatus
     */
    public function getContractualStatusById(int $id): ContractualStatus
    {
        return ContractualStatus::findOrFail($id);
    }

    /**
     * Actualiza un estado contractual usando asignación masiva.
     *
     * @param ContractualStatus $contractualStatus
     * @param array $data
     * @return ContractualStatus
     */
    public function updateContractualStatus(ContractualStatus $contractualStatus, array $data): ContractualStatus
    {
        $contractualStatus->update($data);
        return $contractualStatus;
    }

    /**
     * Elimina un estado contractual.
     *
     * @param ContractualStatus $contractualStatus
     * @return ContractualStatus
     */
    public function deleteContractualStatus(ContractualStatus $contractualStatus): ContractualStatus
    {
        $contractualStatus->delete();
        return $contractualStatus;
    }
}
