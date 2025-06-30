<?php

namespace App\Console\Commands;

use App\Services\AnnualPurchasePlanService;
use Illuminate\Console\Command;

class CreateAnnualPurchasePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-plans:create-annual {--year=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea automáticamente planes de compra para todas las direcciones municipales del año actual';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $year = $this->option('year') ?? date('Y');
        $force = $this->option('force');

        $this->info("Creando planes de compra para el año {$year}...");

        $service = new AnnualPurchasePlanService();
        $result = $service->createAnnualPurchasePlans($year, $force);

        if (!$result['success']) {
            $this->error($result['message']);
            return 1;
        }

        $this->info($result['message']);
        $this->info("- Total direcciones: {$result['total_directions']}");
        $this->info("- Planes creados: {$result['created']}");
        $this->info("- Planes omitidos: {$result['skipped']}");

        if (!empty($result['errors'])) {
            $this->warn("Errores encontrados:");
            foreach ($result['errors'] as $error) {
                $this->error("- {$error}");
            }
        }

        return 0;
    }
}
