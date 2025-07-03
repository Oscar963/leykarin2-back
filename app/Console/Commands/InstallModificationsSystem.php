<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallModificationsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:install {--fresh : Ejecutar migraciones fresh} {--with-examples : Incluir datos de ejemplo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instala el sistema completo de modificaciones de planes de compra';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Instalando Sistema de Modificaciones...');
        
        try {
            // 1. Ejecutar migraciones
            $this->info('📦 Ejecutando migraciones...');
            
            if ($this->option('fresh')) {
                $this->warn('⚠️  Ejecutando migraciones fresh (esto eliminará datos existentes)');
                Artisan::call('migrate:fresh');
            } else {
                Artisan::call('migrate');
            }
            
            $this->info('✅ Migraciones ejecutadas correctamente');
            
            // 2. Verificar que las tablas existen
            $this->info('🔍 Verificando estructura de base de datos...');
            
            $tables = ['modification_types', 'modifications', 'modification_histories', 'modification_files'];
            foreach ($tables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    $this->error("❌ La tabla '{$table}' no existe");
                    return 1;
                }
                $this->info("✅ Tabla '{$table}' verificada");
            }
            
            // 3. Verificar campos en la tabla modifications
            $this->info('🔍 Verificando campos en tabla modifications...');
            $modificationColumns = DB::getSchemaBuilder()->getColumnListing('modifications');
            $requiredColumns = [
                'modification_type_id', 'budget_impact', 'description', 'justification',
                'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason'
            ];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $modificationColumns)) {
                    $this->error("❌ El campo '{$column}' no existe en la tabla modifications");
                    return 1;
                }
                $this->info("✅ Campo '{$column}' verificado");
            }
            
            // 4. Ejecutar seeders
            $this->info('🌱 Ejecutando seeders...');
            
            // Seeder de tipos de modificación
            if (class_exists('Database\Seeders\ModificationTypeSeeder')) {
                Artisan::call('db:seed', ['--class' => 'ModificationTypeSeeder']);
                $this->info('✅ Tipos de modificación instalados');
            } else {
                $this->warn('⚠️  Seeder de tipos de modificación no encontrado');
            }
            
            // Seeder de permisos de modificaciones
            if (class_exists('Database\Seeders\ModificationPermissionSeeder')) {
                Artisan::call('db:seed', ['--class' => 'ModificationPermissionSeeder']);
                $this->info('✅ Permisos de modificaciones instalados');
            } else {
                $this->warn('⚠️  Seeder de permisos de modificaciones no encontrado');
            }
            
            // Seeder de ejemplos de modificaciones (opcional)
            if ($this->option('with-examples')) {
                if (class_exists('Database\Seeders\ModificationExampleSeeder')) {
                    Artisan::call('db:seed', ['--class' => 'ModificationExampleSeeder']);
                    $this->info('✅ Datos de ejemplo de modificaciones creados');
                } else {
                    $this->warn('⚠️  Seeder de ejemplos de modificaciones no encontrado');
                }
            }
            
            // 5. Instalar permisos
            $this->info('🔐 Instalando permisos...');
            Artisan::call('permission:cache-reset');
            
            // 6. Limpiar caché
            $this->info('🧹 Limpiando caché...');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            
            // 7. Generar documentación API
            $this->info('📚 Generando documentación API...');
            if (class_exists('L5Swagger\Generator')) {
                Artisan::call('l5-swagger:generate');
                $this->info('✅ Documentación API generada');
            } else {
                $this->warn('⚠️  L5-Swagger no está instalado');
            }
            
            // 8. Verificar configuración
            $this->info('⚙️  Verificando configuración...');
            
            // Verificar que el modelo PurchasePlan tiene la relación con modificaciones
            if (!method_exists(\App\Models\PurchasePlan::class, 'modifications')) {
                $this->warn('⚠️  La relación modifications no está definida en PurchasePlan');
            } else {
                $this->info('✅ Relación modifications verificada en PurchasePlan');
            }
            
            // Verificar que el modelo User tiene las relaciones necesarias
            $userRelations = ['createdModifications', 'updatedModifications', 'approvedModifications', 'rejectedModifications'];
            foreach ($userRelations as $relation) {
                if (!method_exists(\App\Models\User::class, $relation)) {
                    $this->warn("⚠️  La relación '{$relation}' no está definida en User");
                } else {
                    $this->info("✅ Relación '{$relation}' verificada en User");
                }
            }
            
            $this->info('🎉 Sistema de modificaciones instalado correctamente!');
            
            // Mostrar información de uso
            $this->newLine();
            $this->info('📋 Información de uso:');
            $this->line('• Endpoint principal: /api/modifications');
            $this->line('• Estados disponibles: active, inactive, pending, approved, rejected');
            $this->line('• Tipos principales: Eliminar (Cualitativa/Cuantitativa), Agregar y/o Cambiar, Eliminar y/o Agregar, Agregar');
            $this->line('• Tipos específicos: Incremento/Decremento de Presupuesto, Cambio de Especificaciones, etc.');
            $this->line('• Documentación: /api/documentation (si L5-Swagger está instalado)');
            
            $this->newLine();
            $this->info('🔧 Comandos útiles:');
            $this->line('• php artisan modifications:install --fresh (reinstalar completamente)');
            $this->line('• php artisan modifications:install --with-examples (incluir datos de ejemplo)');
            $this->line('• php artisan modifications:update-types (actualizar solo tipos)');
            $this->line('• php artisan route:list --name=modifications (ver rutas de modificaciones)');
            $this->line('• php artisan tinker (para probar el modelo Modification)');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante la instalación: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
} 