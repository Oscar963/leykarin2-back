<?php

namespace App\Services;

use App\Models\ItemPurchase;
use App\Models\Project;
use Illuminate\Support\Str;

class ItemPurchaseService
{
    /**
     * Estado por defecto para nuevos items de compra
     */
    private const DEFAULT_STATUS_ID = 1; // Solicitado

    /**
     * Obtiene todos los items de compra con sus relaciones
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllItemPurchases()
    {
        return ItemPurchase::with(['budgetAllocation', 'typePurchase'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Obtiene items de compra paginados filtrados por token del proyecto
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @param string|null $projectToken Token del proyecto
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllItemPurchasesByQuery(?string $query, int $perPage = 15, ?string $projectToken = null)
    {
        $project = $this->getProjectByToken($projectToken);
        $projectId = $project->id;

        $queryBuilder = ItemPurchase::with(['budgetAllocation', 'typePurchase'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('product_service', 'LIKE', "%{$query}%")
                    ->orWhereHas('budgetAllocation', function ($q2) use ($query) {
                        $q2->where('description', 'LIKE', "%{$query}%");
                    });
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un item de compra por su ID
     *
     * @param int $id ID del item de compra
     * @return ItemPurchase
     */
    public function getItemPurchaseById($id)
    {
        return ItemPurchase::findOrFail($id);
    }

    /**
     * Obtiene un proyecto por su token
     *
     * @param string $token Token del proyecto
     * @return Project|null
     */
    public function getProjectById($id)
    {
        return Project::findOrFail($id);
    }

    public function getProjectByToken($token)
    {
        return Project::where('token', $token)->firstOrFail();
    }

    /**
     * Crea un nuevo item de compra
     *
     * @param array $data Datos del item de compra
     * @return ItemPurchase
     */
    public function createItemPurchase(array $data)
    {
        $project = $this->getProjectById($data['project_id']);
        
        // Validar que no se exceda el presupuesto del FormF1
        $this->validateBudgetLimit($project, $data);

        $itemPurchase = new ItemPurchase();
        $itemPurchase->product_service = trim($data['product_service']);
        $itemPurchase->quantity_item = $data['quantity_item'];
        $itemPurchase->amount_item = $data['amount_item'];
        $itemPurchase->item_number = $project->getNextItemNumber();
        $itemPurchase->status_item_purchase_id = self::DEFAULT_STATUS_ID;
        $itemPurchase->quantity_oc = $data['quantity_oc'];
        $itemPurchase->months_oc = $data['months_oc'];
        $itemPurchase->regional_distribution = $data['regional_distribution'];
        $itemPurchase->cod_budget_allocation_type = $data['cod_budget_allocation_type'];
        $itemPurchase->publication_month_id = $data['publication_month_id'] ?? null;

        // Relaciones
        $itemPurchase->project_id = $project->id;
        $itemPurchase->budget_allocation_id = $data['budget_allocation_id'];
        $itemPurchase->type_purchase_id = $data['type_purchase_id'];
        $itemPurchase->created_by = auth()->id();
        $itemPurchase->save();

        return $itemPurchase;
    }

    /**
     * Valida que el monto total de todos los proyectos no exceda el presupuesto del FormF1
     *
     * @param Project $project Proyecto al que se agregará el ítem
     * @param array $data Datos del nuevo ítem
     * @throws \Exception Si se excede el presupuesto
     */
    private function validateBudgetLimit(Project $project, array $data)
    {
        $purchasePlan = $project->purchasePlan;
        
        if (!$purchasePlan || !$purchasePlan->formF1) {
            throw new \Exception('No se encontró el FormF1 asociado al plan de compra.');
        }

        $formF1Amount = $purchasePlan->formF1->amount;
        $currentTotalAmount = $purchasePlan->getTotalAmount();
        $newItemAmount = $data['amount_item'] * $data['quantity_item'];
        $projectedTotal = $currentTotalAmount + $newItemAmount;

        if ($projectedTotal > $formF1Amount) {
            throw new \Exception(
                "El monto total excede el presupuesto disponible del Formulario F1. " .
                "Presupuesto disponible: $" . number_format($formF1Amount - $currentTotalAmount, 0, ',', '.') . ". " .
                "Ajuste el monto o reduzca otros ítems."
            );
        }
    }

    /**
     * Actualiza un item de compra existente
     *
     * @param int $id ID del item de compra
     * @param array $data Datos actualizados
     * @return ItemPurchase
     */
    public function updateItemPurchase($id, array $data)
    {
        $project = $this->getProjectById($data['project_id']);

        $this->validateBudgetLimit($project, $data); // Validar que no se exceda el presupuesto del FormF1

        $itemPurchase = $this->getItemPurchaseById($id);
        $itemPurchase->product_service = trim($data['product_service']);
        $itemPurchase->quantity_item = $data['quantity_item'];
        $itemPurchase->amount_item = $data['amount_item'];
        $itemPurchase->status_item_purchase_id = self::DEFAULT_STATUS_ID;
        $itemPurchase->quantity_oc = $data['quantity_oc'];
        $itemPurchase->months_oc = $data['months_oc'];
        $itemPurchase->regional_distribution = $data['regional_distribution'];
        $itemPurchase->cod_budget_allocation_type = $data['cod_budget_allocation_type'];
        $itemPurchase->publication_month_id = $data['publication_month_id'] ?? null;

        // Relaciones
        $itemPurchase->project_id = $project->id;
        $itemPurchase->budget_allocation_id = $data['budget_allocation_id'];
        $itemPurchase->type_purchase_id = $data['type_purchase_id'];
        $itemPurchase->updated_by = auth()->id();
        $itemPurchase->save();

        return $itemPurchase;
    }

    /**
     * Elimina un item de compra y reorganiza los números de items restantes
     *
     * @param int $id ID del item de compra
     * @return void
     */
    public function deleteItemPurchase($id)
    {
        $itemPurchase = $this->getItemPurchaseById($id);
        $projectId = $itemPurchase->project_id;

        $itemPurchase->delete();

        $this->reorderItemNumbers($projectId);
    }

    /**
     * Reordena los números de items secuencialmente
     *
     * @param int $projectId ID del proyecto
     * @return void
     */
    private function reorderItemNumbers(int $projectId): void
    {
        $items = ItemPurchase::where('project_id', $projectId)
            ->orderBy('item_number', 'ASC')
            ->get();

        $itemNumber = 1;
        foreach ($items as $item) {
            $item->item_number = $itemNumber;
            $item->save();
            $itemNumber++;
        }
    }

    /**
     * Actualiza el estado de un item de compra
     *
     * @param int $id ID del item de compra
     * @param array $data Datos actualizados
     * @return ItemPurchase
     */

    public function updateItemPurchaseStatus($id, $data)
    {
        $itemPurchase = $this->getItemPurchaseById($id);
        $itemPurchase->status_item_purchase_id = $data['status_item_purchase_id'];
        $itemPurchase->updated_by = auth()->id();
        $itemPurchase->save();
        return $itemPurchase;
    }
}
