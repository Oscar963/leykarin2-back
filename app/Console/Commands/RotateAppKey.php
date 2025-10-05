<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Comando para rotar la APP_KEY de forma segura.
 * 
 * Esto invalida todas las sesiones activas y requiere que los usuarios
 * vuelvan a iniciar sesión.
 */
class RotateAppKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:rotate {--force : Forzar rotación sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rota la APP_KEY de forma segura manteniendo compatibilidad';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de rotar la APP_KEY? Esto cerrará todas las sesiones activas.')) {
                $this->info('Rotación cancelada.');
                return 0;
            }
        }

        $this->info('🔄 Iniciando rotación de APP_KEY...');

        // Backup del .env actual
        $envPath = base_path('.env');
        $backupPath = base_path('.env.backup.' . now()->format('YmdHis'));
        
        if (File::exists($envPath)) {
            File::copy($envPath, $backupPath);
            $this->info("✅ Backup creado: {$backupPath}");
        } else {
            $this->error('❌ Archivo .env no encontrado');
            return 1;
        }

        // Obtener la clave actual
        $oldKey = config('app.key');

        // Generar nueva clave
        Artisan::call('key:generate', ['--force' => true]);
        $this->info('✅ Nueva APP_KEY generada');

        // Limpiar cachés
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        // Limpiar sesiones si el driver lo soporta
        try {
            Artisan::call('session:flush');
            $this->info('✅ Sesiones limpiadas');
        } catch (\Exception $e) {
            $this->warn('⚠️  No se pudieron limpiar las sesiones automáticamente');
        }

        $this->info('✅ Cachés limpiados');

        // Log de seguridad
        Log::channel('security')->warning('APP_KEY rotada', [
            'timestamp' => now()->toDateTimeString(),
            'user' => auth()->user()->email ?? 'CLI',
            'backup' => $backupPath,
            'environment' => config('app.env')
        ]);

        $this->newLine();
        $this->info('✅ Rotación completada exitosamente');
        $this->warn('⚠️  Todas las sesiones activas han sido cerradas');
        $this->warn('⚠️  Los usuarios deberán iniciar sesión nuevamente');
        $this->warn('⚠️  Backup guardado en: ' . $backupPath);

        return 0;
    }
}
