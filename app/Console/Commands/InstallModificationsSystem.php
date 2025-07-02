<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallModificationsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instala el sistema de modificaciones con migraciones y permisos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando instalación del sistema de modificaciones...');

        try {
            // Ejecutar migraciones
            $this->info('Ejecutando migraciones...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✓ Migraciones ejecutadas correctamente');

            // Ejecutar seeder de permisos
            $this->info('Instalando permisos...');
            Artisan::call('db:seed', [
                '--class' => 'ModificationPermissionSeeder',
                '--force' => true
            ]);
            $this->info('✓ Permisos instalados correctamente');

            // Limpiar caché
            $this->info('Limpiando caché...');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            $this->info('✓ Caché limpiado correctamente');

            $this->info('¡Sistema de modificaciones instalado exitosamente!');
            $this->info('');
            $this->info('Endpoints disponibles:');
            $this->info('- GET /api/modifications - Listar modificaciones');
            $this->info('- POST /api/modifications - Crear modificación');
            $this->info('- GET /api/modifications/{id} - Ver modificación');
            $this->info('- PUT /api/modifications/{id} - Actualizar modificación');
            $this->info('- DELETE /api/modifications/{id} - Eliminar modificación');
            $this->info('- PUT /api/modifications/{id}/status - Cambiar estado');
            $this->info('- GET /api/modifications/statuses - Estados disponibles');
            $this->info('- GET /api/purchase-plans/{id}/modifications - Modificaciones por plan');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error durante la instalación: ' . $e->getMessage());
            return 1;
        }
    }
} 