<?php

namespace App\Console\Commands;

use App\Models\ItemPurchase;
use App\Models\PurchasePlan;
use App\Services\ItemPurchaseService;
use Illuminate\Console\Command;

class TestItemStatusValidation extends Command
{
    protected $signature = 'test:item-status-validation {item_id} {new_status_id}';
    protected $description = 'Prueba la validación de cambio de estado de ítems según el estado del plan de compra';

    protected $itemPurchaseService;

    public function __construct(ItemPurchaseService $itemPurchaseService)
    {
        parent::__construct();
        $this->itemPurchaseService = $itemPurchaseService;
    }

    public function handle()
    {
        $itemId = $this->argument('item_id');
        $newStatusId = $this->argument('new_status_id');

        $this->info("🧪 Probando validación de cambio de estado...");
        $this->info("Item ID: {$itemId}");
        $this->info("Nuevo estado ID: {$newStatusId}");
        $this->newLine();

        try {
            // Obtener el ítem
            $item = ItemPurchase::with('project.purchasePlan.currentStatus.status')->find($itemId);
            
            if (!$item) {
                $this->error("❌ No se encontró el ítem con ID: {$itemId}");
                return 1;
            }

            $this->info("📋 Información del ítem:");
            $this->info("  - Producto/Servicio: {$item->product_service}");
            $statusName = $item->statusItemPurchase ? $item->statusItemPurchase->name : 'N/A';
            $this->info("  - Estado actual: {$statusName}");
            
            $purchasePlan = $item->project->purchasePlan;
            if ($purchasePlan) {
                $currentStatus = $purchasePlan->getCurrentStatus();
                $statusName = $currentStatus->status ? $currentStatus->status->name : 'Desconocido';
                $statusId = $currentStatus->status_purchase_plan_id;
                
                $this->info("  - Plan de compra: {$purchasePlan->name}");
                $this->info("  - Estado del plan: {$statusName} (ID: {$statusId})");
                
                // Mostrar si el estado permite cambios
                if (in_array($statusId, [6, 7])) {
                    $this->info("  ✅ Estado válido para cambios (Decretado o Publicado)");
                } else {
                    $this->warn("  ⚠️  Estado NO válido para cambios. Debe ser 'Decretado' (6) o 'Publicado' (7)");
                }
            } else {
                $this->error("  ❌ No se encontró plan de compra asociado");
            }

            $this->newLine();
            $this->info("🔄 Intentando cambiar estado...");

            // Intentar cambiar el estado
            $result = $this->itemPurchaseService->updateItemPurchaseStatus($itemId, [
                'status_item_purchase_id' => $newStatusId
            ]);

            $this->info("✅ ¡Cambio de estado exitoso!");
            $this->info("  - Nuevo estado ID: {$result->status_item_purchase_id}");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error al cambiar estado:");
            $this->error("  {$e->getMessage()}");
            
            if (str_contains($e->getMessage(), 'No es posible cambiar el estado de los ítems')) {
                $this->newLine();
                $this->comment("💡 Esto es correcto: La validación está funcionando como se esperaba.");
                $this->comment("   Solo se pueden cambiar estados cuando el plan está 'Decretado' o 'Publicado'.");
            }
            
            return 1;
        }
    }
} 