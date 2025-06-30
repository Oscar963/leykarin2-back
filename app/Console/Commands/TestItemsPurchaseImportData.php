<?php

namespace App\Console\Commands;

use App\Models\PublicationMonth;
use App\Models\TypePurchase;
use App\Models\BudgetAllocation;
use App\Models\Project;
use App\Models\StatusItemPurchase;
use App\Models\ItemPurchase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestItemsPurchaseImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:items-purchase-import-data {--project-id= : ID del proyecto para la prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la importación de items de compra con datos de prueba';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Prueba de Importación Optimizada ===');

        // Obtener un proyecto de prueba
        $project = Project::first();
        if (!$project) {
            $this->error('No hay proyectos disponibles.');
            return 1;
        }

        $projectId = $project->id;
        $this->info("Usando proyecto: {$project->name} (ID: {$projectId})");

        // Probar la clase de importación optimizada
        $this->info('1. Inicializando clase de importación optimizada...');
        $startTime = microtime(true);

        $import = new \App\Imports\ItemsPurchaseImport($projectId);
        $sheetImport = $import->sheetImport;

        $endTime = microtime(true);
        $initTime = round(($endTime - $startTime) * 1000, 2);

        $this->line("   - Tiempo de inicialización: {$initTime}ms");
        $this->line("   - Caché de asignaciones presupuestarias: " . count($sheetImport->budgetAllocationsCache) . " elementos");
        $this->line("   - Caché de tipos de compra: " . count($sheetImport->typePurchasesCache) . " elementos");
        $this->line("   - Caché de meses de publicación: " . count($sheetImport->publicationMonthsCache) . " elementos");
        $this->line("   - ID de estado por defecto: {$sheetImport->defaultStatusId}");

        // Probar búsquedas en caché
        $this->info('2. Probando búsquedas en caché...');

        $budgetAllocation = BudgetAllocation::first();
        $typePurchase = TypePurchase::first();
        $publicationMonth = PublicationMonth::first();

        if ($budgetAllocation && $typePurchase && $publicationMonth) {
            $startTime = microtime(true);

            $budgetId = $sheetImport->getBudgetAllocationIdFromCache($budgetAllocation->code);
            $typeId = $sheetImport->getTypePurchaseIdFromCache($typePurchase->name);
            $monthId = $sheetImport->getPublicationMonthIdFromCache($publicationMonth->short_name . ' ' . $publicationMonth->year);

            $endTime = microtime(true);
            $searchTime = round(($endTime - $startTime) * 1000, 2);

            $this->line("   - Tiempo de búsqueda en caché: {$searchTime}ms");
            $this->line("   - Asignación presupuestaria encontrada: " . ($budgetId ? 'Sí' : 'No'));
            $this->line("   - Tipo de compra encontrado: " . ($typeId ? 'Sí' : 'No'));
            $this->line("   - Mes de publicación encontrado: " . ($monthId ? 'Sí' : 'No'));
        }

        // Probar creación de item
        $this->info('3. Probando creación de item...');

        $startTime = microtime(true);

        try {
            $itemPurchase = new ItemPurchase([
                'item_number' => 999,
                'product_service' => 'Prueba de importación optimizada',
                'quantity_item' => 1,
                'amount_item' => 1000,
                'quantity_oc' => 1,
                'months_oc' => 'Enero',
                'regional_distribution' => 'Región Metropolitana',
                'cod_budget_allocation_type' => 'TEST',
                'project_id' => $projectId,
                'budget_allocation_id' => $sheetImport->defaultStatusId,
                'type_purchase_id' => $sheetImport->defaultStatusId,
                'status_item_purchase_id' => $sheetImport->defaultStatusId,
                'publication_month_id' => $sheetImport->defaultStatusId,
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            $itemPurchase->save();

            $endTime = microtime(true);
            $createTime = round(($endTime - $startTime) * 1000, 2);

            $this->line("   - Tiempo de creación: {$createTime}ms");
            $this->line("   - Item creado con ID: {$itemPurchase->id}");

            // Eliminar el item de prueba
            $itemPurchase->delete();
            $this->line("   - Item de prueba eliminado");
        } catch (\Exception $e) {
            $this->error("   - Error al crear item: " . $e->getMessage());
        }

        $this->info('=== Prueba completada ===');
        return 0;
    }

    /**
     * Obtener ID de asignación presupuestaria por código
     */
    protected function getBudgetAllocationId($code)
    {
        $allocation = BudgetAllocation::where('code', $code)->first();
        return $allocation ? $allocation->id : null;
    }

    /**
     * Obtener ID de tipo de compra por nombre
     */
    protected function getTypePurchaseId($name)
    {
        $type = TypePurchase::where('name', $name)->first();
        return $type ? $type->id : null;
    }

    /**
     * Obtener ID de mes de publicación
     */
    protected function getPublicationMonthId($monthYear)
    {
        if (empty($monthYear)) {
            return null;
        }

        $monthYear = trim($monthYear);

        // Buscar por nombre corto y año (formato "Dic 2025")
        $parts = explode(' ', $monthYear);
        if (count($parts) >= 2) {
            $shortName = $parts[0];
            $year = $parts[1];

            $publicationMonth = PublicationMonth::where('short_name', $shortName)
                ->where('year', $year)
                ->first();

            if ($publicationMonth) {
                return $publicationMonth->id;
            }
        }

        // Buscar por nombre completo y año (formato "Diciembre 2025")
        if (count($parts) >= 2) {
            $name = $parts[0];
            $year = $parts[1];

            $publicationMonth = PublicationMonth::where('name', $name)
                ->where('year', $year)
                ->first();

            if ($publicationMonth) {
                return $publicationMonth->id;
            }
        }

        // Buscar solo por nombre corto (asumiendo año actual)
        $currentYear = date('Y');
        $publicationMonth = PublicationMonth::where('short_name', $monthYear)
            ->where('year', $currentYear)
            ->first();

        if ($publicationMonth) {
            return $publicationMonth->id;
        }

        return null;
    }
}
