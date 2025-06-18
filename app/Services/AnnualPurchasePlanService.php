<?php

namespace App\Services;

use App\Models\Direction;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnnualPurchasePlanService
{
    /**
     * Crea planes de compra automáticamente para todas las direcciones municipales
     *
     * @param int|null $year
     * @param bool $force
     * @return array
     */
    public function createAnnualPurchasePlans($year = null, $force = false): array
    {
        $year = $year ?? date('Y');
        
        Log::info("Iniciando creación de planes de compra para el año {$year}");

        // Obtener todas las direcciones
        $directions = Direction::all();

        if ($directions->isEmpty()) {
            Log::warning('No se encontraron direcciones municipales registradas.');
            return [
                'success' => false,
                'message' => 'No se encontraron direcciones municipales registradas.',
                'created' => 0,
                'skipped' => 0,
                'errors' => []
            ];
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($directions as $direction) {
            try {
                $result = $this->createPurchasePlanForDirection($direction, $year, $force);
                
                if ($result['created']) {
                    $created++;
                } elseif ($result['skipped']) {
                    $skipped++;
                }
                
                if (isset($result['error'])) {
                    $errors[] = $result['error'];
                }
                
            } catch (\Exception $e) {
                $error = "Error al crear plan para {$direction->name}: " . $e->getMessage();
                Log::error($error);
                $errors[] = $error;
            }
        }

        $message = "Proceso completado - Creados: {$created}, Omitidos: {$skipped}";
        Log::info($message);

        return [
            'success' => true,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_directions' => $directions->count()
        ];
    }

    /**
     * Crea un plan de compra para una dirección específica
     *
     * @param Direction $direction
     * @param int $year
     * @param bool $force
     * @return array
     */
    private function createPurchasePlanForDirection(Direction $direction, int $year, bool $force): array
    {
        // Verificar si ya existe un plan de compra para esta dirección y año
        $existingPlan = PurchasePlan::where('direction_id', $direction->id)
            ->where('year', $year)
            ->first();

        if ($existingPlan && !$force) {
            Log::info("Plan de compra ya existe para {$direction->name} - {$year}. Omitiendo...");
            return [
                'created' => false,
                'skipped' => true,
                'message' => "Plan ya existe para {$direction->name}"
            ];
        }

        // Preparar datos del plan de compra
        $planData = [
            'name' => "{$direction->name} - {$year}",
            'token' => $this->generateUniqueToken(),
            'date_created' => now(),
            'year' => $year,
            'direction_id' => $direction->id,
            'created_by' => $direction->director_id,
        ];

        if ($existingPlan && $force) {
            // Actualizar plan existente
            $existingPlan->update($planData);
            
            // Crear estado inicial si no existe
            $this->createInitialStatus($existingPlan);
            
            Log::info("Plan de compra actualizado para: {$direction->name}");
            return [
                'created' => false,
                'skipped' => false,
                'updated' => true,
                'message' => "Plan actualizado para {$direction->name}"
            ];
        } else {
            // Crear nuevo plan
            $newPlan = PurchasePlan::create($planData);
            
            // Crear estado inicial
            $this->createInitialStatus($newPlan);
            
            Log::info("Plan de compra creado para: {$direction->name}");
            return [
                'created' => true,
                'skipped' => false,
                'plan' => $newPlan,
                'message' => "Plan creado para {$direction->name}"
            ];
        }
    }

    /**
     * Crea el estado inicial (Borrador) para un plan de compra
     *
     * @param PurchasePlan $purchasePlan
     * @return void
     */
    private function createInitialStatus(PurchasePlan $purchasePlan): void
    {
        // Verificar si ya existe un estado inicial
        $existingStatus = PurchasePlanStatus::where('purchase_plan_id', $purchasePlan->id)
            ->where('status_purchase_plan_id', 1) // Borrador
            ->first();

        if (!$existingStatus) {
            PurchasePlanStatus::create([
                'purchase_plan_id' => $purchasePlan->id,
                'status_purchase_plan_id' => 1, // Borrador
                'sending_date' => now(),
                'plan_name' => $purchasePlan->name,
                'plan_year' => $purchasePlan->year,
                'total_amount' => 0,
                'available_budget' => 0,
                'sending_comment' => 'Plan de compra creado automáticamente en estado borrador',
                'created_by' => $purchasePlan->created_by,
            ]);
        }
    }

    /**
     * Genera un token único para el plan de compra
     *
     * @return string
     */
    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (PurchasePlan::where('token', $token)->exists());

        return $token;
    }

    /**
     * Verifica si es junio y es apropiado crear los planes
     *
     * @return bool
     */
    public function shouldCreateAnnualPlans(): bool
    {
        return date('n') == 6; // Junio es el mes 6
    }
} 