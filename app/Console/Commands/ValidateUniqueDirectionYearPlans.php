<?php

namespace App\Console\Commands;

use App\Models\PurchasePlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateUniqueDirectionYearPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:validate-unique-direction-year 
                            {--fix : Corregir automáticamente los planes duplicados eliminando los más recientes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida que no existan planes de compras duplicados por dirección y año';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Validando planes de compras únicos por dirección y año...');

        // Buscar planes duplicados
        $duplicates = $this->findDuplicatePlans();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No se encontraron planes de compras duplicados por dirección y año.');
            return 0;
        }

        $this->warn("⚠️  Se encontraron {$duplicates->count()} grupos de planes duplicados:");

        foreach ($duplicates as $duplicate) {
            $this->displayDuplicateGroup($duplicate);
        }

        if ($this->option('fix')) {
            $this->fixDuplicatePlans($duplicates);
        } else {
            $this->info("\n💡 Para corregir automáticamente los planes duplicados, ejecuta:");
            $this->line('   php artisan plans:validate-unique-direction-year --fix');
        }

        return 0;
    }

    /**
     * Encuentra planes duplicados por dirección y año
     */
    private function findDuplicatePlans()
    {
        return DB::table('purchase_plans')
            ->select('direction_id', 'year', DB::raw('COUNT(*) as count'))
            ->groupBy('direction_id', 'year')
            ->having('count', '>', 1)
            ->get();
    }

    /**
     * Muestra información detallada de un grupo de planes duplicados
     */
    private function displayDuplicateGroup($duplicate)
    {
        $direction = \App\Models\Direction::find($duplicate->direction_id);
        $directionName = $direction ? $direction->name : "ID: {$duplicate->direction_id}";

        $this->line("\n📋 Dirección: {$directionName} | Año: {$duplicate->year} | Cantidad: {$duplicate->count}");

        $plans = PurchasePlan::where('direction_id', $duplicate->direction_id)
            ->where('year', $duplicate->year)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($plans as $plan) {
            $status = $plan->getCurrentStatusName() ?? 'Sin estado';
            $createdBy = $plan->createdBy ? $plan->createdBy->name : 'N/A';

            $this->line("   • ID: {$plan->id} | Nombre: {$plan->name} | Estado: {$status} | Creado por: {$createdBy} | Fecha: {$plan->created_at}");
        }
    }

    /**
     * Corrige los planes duplicados eliminando los más recientes
     */
    private function fixDuplicatePlans($duplicates)
    {
        $this->warn("\n🔧 Corrigiendo planes duplicados...");

        $deletedCount = 0;

        foreach ($duplicates as $duplicate) {
            $plans = PurchasePlan::where('direction_id', $duplicate->direction_id)
                ->where('year', $duplicate->year)
                ->orderBy('created_at', 'asc')
                ->get();

            // Mantener el plan más antiguo, eliminar los demás
            $plansToDelete = $plans->skip(1);

            foreach ($plansToDelete as $plan) {
                $direction = \App\Models\Direction::find($duplicate->direction_id);
                $directionName = $direction ? $direction->name : "ID: {$duplicate->direction_id}";

                $this->line("   🗑️  Eliminando plan ID: {$plan->id} ({$plan->name}) - {$directionName} {$duplicate->year}");

                // Registrar antes de eliminar
                \App\Models\HistoryPurchaseHistory::logAction(
                    $plan->id,
                    'delete_duplicate',
                    'Plan de compra eliminado por duplicado',
                    [
                        'name' => $plan->name,
                        'year' => $plan->year,
                        'direction' => $directionName,
                        'reason' => 'Plan duplicado - mantenido el más antiguo'
                    ]
                );

                $plan->delete();
                $deletedCount++;
            }
        }

        $this->info("✅ Se eliminaron {$deletedCount} planes duplicados.");
        $this->info("✅ Validación completada. Todos los planes ahora son únicos por dirección y año.");
    }
}
