<?php

namespace App\Jobs;

use App\Services\AnnualPurchasePlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateAnnualPurchasePlansJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $year;
    private $force;

    /**
     * Create a new job instance.
     *
     * @param int|null $year
     * @param bool $force
     */
    public function __construct($year = null, $force = false)
    {
        $this->year = $year ?? date('Y');
        $this->force = $force;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Iniciando creación automática de planes de compra para el año {$this->year}");

        try {
            $service = new AnnualPurchasePlanService();
            $result = $service->createAnnualPurchasePlans($this->year, $this->force);

            if (!$result['success']) {
                Log::error($result['message']);
                return;
            }

            Log::info("Job completado exitosamente: {$result['message']}");

            if (!empty($result['errors'])) {
                Log::warning("Errores encontrados durante el proceso:");
                foreach ($result['errors'] as $error) {
                    Log::error($error);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al crear planes de compra automáticamente: " . $e->getMessage());
            throw $e;
        }
    }
}
