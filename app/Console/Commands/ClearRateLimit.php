<?php

namespace App\Console\Commands;

use App\Services\Security\RateLimitService;
use Illuminate\Console\Command;

class ClearRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limit:clear {user_id : ID del usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar el rate limit de un usuario específico';

    /**
     * Execute the console command.
     */
    public function handle(RateLimitService $rateLimitService)
    {
        $userId = $this->argument('user_id');
        
        try {
            $rateLimitService->clearLimits($userId);
            $this->info("✅ Rate limit limpiado para el usuario ID: {$userId}");
        } catch (\Exception $e) {
            $this->error("❌ Error al limpiar rate limit: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 