<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallGoalsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goals:install {--force : Forzar la instalación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instala el sistema de metas para proyectos estratégicos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Iniciando instalación del Sistema de Metas...');
        $this->newLine();

        try {
            // 1. Ejecutar migraciones
            $this->info('📊 Ejecutando migraciones...');
            Artisan::call('migrate', ['--force' => $this->option('force')]);
            $this->info('✅ Migraciones ejecutadas correctamente');
            $this->newLine();

            // 2. Ejecutar seeder de permisos
            $this->info('🔐 Creando permisos para metas...');
            Artisan::call('db:seed', ['--class' => 'GoalPermissionSeeder', '--force' => true]);
            $this->info('✅ Permisos creados correctamente');
            $this->newLine();

            // 3. Limpiar cache de permisos
            $this->info('🧹 Limpiando cache de permisos...');
            Artisan::call('permission:cache-reset');
            $this->info('✅ Cache de permisos limpiado');
            $this->newLine();

            // 4. Mostrar resumen
            $this->displaySummary();

            $this->info('🎉 ¡Sistema de Metas instalado exitosamente!');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la instalación: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Muestra un resumen de lo que se instaló
     */
    private function displaySummary()
    {
        $this->info('📋 Resumen de instalación:');
        $this->line('');
        $this->line('✅ Tabla "goals" creada');
        $this->line('✅ Modelo Goal configurado');
        $this->line('✅ Controlador GoalController creado');
        $this->line('✅ Servicio GoalService configurado');
        $this->line('✅ Resource GoalResource creado');
        $this->line('✅ Middleware ValidateStrategicProject registrado');
        $this->line('✅ Rutas API configuradas');
        $this->line('✅ Permisos por rol asignados');
        $this->line('✅ Integración con Project completada');
        $this->line('');
        
        $this->comment('📚 Endpoints disponibles:');
        $this->line('  GET    /api/goals                          - Listar metas');
        $this->line('  POST   /api/goals                          - Crear meta');
        $this->line('  GET    /api/goals/{id}                     - Ver meta');
        $this->line('  PUT    /api/goals/{id}                     - Actualizar meta');
        $this->line('  DELETE /api/goals/{id}                     - Eliminar meta');
        $this->line('  PUT    /api/goals/{id}/progress            - Actualizar progreso');
        $this->line('  GET    /api/goals/project/{id}/statistics  - Estadísticas');
        $this->line('  GET    /api/goals/overdue                  - Metas vencidas');
        $this->line('');
        
        $this->comment('📖 Documentación: README_METAS_PROYECTOS_ESTRATEGICOS.md');
        $this->newLine();
    }
} 