<?php

namespace App\Console\Commands;

use App\Services\PublicationMonthService;
use Illuminate\Console\Command;

class TestPublicationMonthService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:publication-month-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba todas las funcionalidades del PublicationMonthService';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new PublicationMonthService();

        $this->info('=== Prueba del PublicationMonthService ===');

        // 1. Obtener todos los meses
        $this->info('1. Obtener todos los meses:');
        $allMonths = $service->getAllPublicationMonths();
        $this->line("   Total de meses: {$allMonths->count()}");

        // 2. Obtener años disponibles
        $this->info('2. Años disponibles:');
        $years = $service->getAvailableYears();
        foreach ($years as $year) {
            $this->line("   - {$year->year}");
        }

        // 3. Obtener meses por año
        $this->info('3. Meses por año (2025):');
        $months2025 = $service->getPublicationMonthsByYear(2025);
        foreach ($months2025 as $month) {
            $this->line("   - {$month->formatted_date}");
        }

        // 4. Obtener meses para select
        $this->info('4. Meses para select:');
        $selectMonths = $service->getPublicationMonthsForSelect();
        foreach ($selectMonths->take(5) as $month) {
            $this->line("   - {$month['display_name']} (ID: {$month['id']})");
        }

        // 5. Estadísticas
        $this->info('5. Estadísticas:');
        $stats = $service->getPublicationMonthsStats();
        $this->line("   - Total de meses: {$stats['total_months']}");
        $this->line("   - Total de años: {$stats['total_years']}");
        $this->line("   - Meses con ítems: {$stats['months_with_items']}");
        $this->line("   - Meses sin usar: {$stats['unused_months']}");
        $this->line("   - Porcentaje de uso: {$stats['usage_percentage']}%");

        // 6. Búsqueda
        $this->info('6. Búsqueda por "Enero":');
        $searchResults = $service->searchPublicationMonths('Enero', 5);
        foreach ($searchResults as $month) {
            $this->line("   - {$month->formatted_date}");
        }

        // 7. Crear meses para un nuevo año (solo si no existe)
        $this->info('7. Crear meses para 2027:');
        try {
            $createdMonths = $service->createMonthsForYear(2027);
            $this->line("   Se crearon {$createdMonths->count()} meses para 2027");
        } catch (\Exception $e) {
            $this->line("   Error: " . $e->getMessage());
        }

        // 8. Paginación
        $this->info('8. Prueba de paginación:');
        $paginated = $service->getPublicationMonthsPaginated(5);
        $this->line("   - Total de registros: {$paginated->total()}");
        $this->line("   - Por página: {$paginated->perPage()}");
        $this->line("   - Página actual: {$paginated->currentPage()}");
        $this->line("   - Última página: {$paginated->lastPage()}");

        $this->info('=== Prueba completada ===');
    }
}
