<?php

namespace App\Console\Commands;

use App\Models\PurchasePlan;
use App\Models\PurchasePlanStatus;
use App\Models\HistoryPurchaseHistory;
use Illuminate\Console\Command;

class FixPurchasePlanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-plan:fix-status {purchase_plan_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir el estado de un plan de compra que tiene decreto pero no está en estado Decretado';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $purchasePlanId = $this->argument('purchase_plan_id');
        
        $this->info("Verificando plan de compra ID: {$purchasePlanId}");
        
        $purchasePlan = PurchasePlan::with(['currentStatus.status', 'decreto'])->find($purchasePlanId);
        
        if (!$purchasePlan) {
            $this->error("Plan de compra con ID {$purchasePlanId} no encontrado.");
            return Command::FAILURE;
        }
        
        $this->info("Plan de compra encontrado:");
        $this->line("  - Nombre: {$purchasePlan->name}");
        $this->line("  - Año: {$purchasePlan->year}");
        $this->line("  - Dirección: {$purchasePlan->direction->name}");
        $this->line("  - Decreto ID: " . ($purchasePlan->decreto_id ?? 'NULL'));
        
        $currentStatus = $purchasePlan->getCurrentStatus();
        $currentStatusName = $currentStatus && $currentStatus->status ? $currentStatus->status->name : 'Desconocido';
        $currentStatusId = $currentStatus ? $currentStatus->status_purchase_plan_id : null;
        
        $this->line("  - Estado actual: {$currentStatusName} (ID: {$currentStatusId})");
        
        // Verificar si tiene decreto pero no está en estado Decretado
        if ($purchasePlan->decreto_id && $currentStatusId !== 6) {
            $this->warn("⚠️  El plan tiene decreto (ID: {$purchasePlan->decreto_id}) pero NO está en estado 'Decretado'");
            
            if ($this->confirm('¿Desea corregir el estado a "Decretado"?')) {
                // Crear nuevo estado "Decretado" (ID: 6)
                $purchasePlanStatus = new PurchasePlanStatus();
                $purchasePlanStatus->purchase_plan_id = $purchasePlan->id;
                $purchasePlanStatus->status_purchase_plan_id = 6; // ID del estado "Decretado"
                $purchasePlanStatus->sending_date = now();
                $purchasePlanStatus->sending_comment = 'Estado corregido automáticamente a "Decretado" (comando de corrección)';
                $purchasePlanStatus->created_by = 1; // Usuario sistema
                $purchasePlanStatus->save();

                // Registrar en el historial
                HistoryPurchaseHistory::logAction(
                    $purchasePlan->id,
                    'status_change',
                    "Estado corregido de '{$currentStatusName}' a 'Decretado' (comando de corrección)",
                    [
                        'old_status' => [
                            'id' => $currentStatusId,
                            'name' => $currentStatusName
                        ],
                        'new_status' => [
                            'id' => 6,
                            'name' => 'Decretado'
                        ],
                        'comment' => 'Estado corregido automáticamente (comando de corrección)'
                    ]
                );
                
                $this->info("✅ Estado corregido exitosamente a 'Decretado'");
                $this->info("✅ Historial actualizado");
                
                return Command::SUCCESS;
            } else {
                $this->info("Operación cancelada.");
                return Command::SUCCESS;
            }
        } else {
            if ($purchasePlan->decreto_id) {
                $this->info("✅ El plan ya está en estado correcto (tiene decreto y está en estado 'Decretado')");
            } else {
                $this->info("ℹ️  El plan no tiene decreto asociado, no requiere corrección");
            }
            return Command::SUCCESS;
        }
    }
} 