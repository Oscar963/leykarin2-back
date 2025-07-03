<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CleanModificationsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:clean {--with-examples : Incluir datos de ejemplo después de la limpieza}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia y reinstala solo el sistema de modificaciones';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🧹 Limpiando Sistema de Modificaciones...');
        
        if (!$this->confirm('¿Estás seguro de que quieres eliminar todos los datos de modificaciones? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return 0;
        }
        
        try {
            // 1. Eliminar datos de modificaciones
            $this->info('🗑️  Eliminando datos de modificaciones...');
            
            // Eliminar en orden para evitar errores de foreign key
            DB::table('modification_histories')->truncate();
            $this->info('✅ Historial de modificaciones eliminado');
            
            DB::table('modification_files')->truncate();
            $this->info('✅ Archivos de modificaciones eliminados');
            
            DB::table('modifications')->truncate();
            $this->info('✅ Modificaciones eliminadas');
            
            DB::table('modification_types')->truncate();
            $this->info('✅ Tipos de modificación eliminados');
            
            // 2. Reinstalar tipos de modificación
            $this->info('🔄 Reinstalando tipos de modificación...');
            if (class_exists('Database\Seeders\ModificationTypeSeeder')) {
                Artisan::call('db:seed', ['--class' => 'ModificationTypeSeeder']);
                $this->info('✅ Tipos de modificación reinstalados');
            }
            
            // 3. Reinstalar permisos
            $this->info('🔐 Reinstalando permisos...');
            if (class_exists('Database\Seeders\ModificationPermissionSeeder')) {
                Artisan::call('db:seed', ['--class' => 'ModificationPermissionSeeder']);
                $this->info('✅ Permisos reinstalados');
            }
            
            // 4. Crear datos de ejemplo si se solicita
            if ($this->option('with-examples')) {
                $this->info('📝 Creando datos de ejemplo...');
                if (class_exists('Database\Seeders\ModificationExampleSeeder')) {
                    Artisan::call('db:seed', ['--class' => 'ModificationExampleSeeder']);
                    $this->info('✅ Datos de ejemplo creados');
                }
            }
            
            // 5. Limpiar caché
            $this->info('🧹 Limpiando caché...');
            Artisan::call('permission:cache-reset');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            
            $this->info('🎉 Sistema de modificaciones limpiado y reinstalado correctamente!');
            
            $this->newLine();
            $this->info('📋 Información:');
            $this->line('• Todos los datos de modificaciones han sido eliminados');
            $this->line('• Los tipos de modificación han sido reinstalados');
            $this->line('• Los permisos han sido reinstalados');
            if ($this->option('with-examples')) {
                $this->line('• Se han creado datos de ejemplo para pruebas');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());
            return 1;
        }
    }
} 