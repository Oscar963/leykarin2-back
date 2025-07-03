<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateModificationTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:update-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los tipos de modificación con los nuevos tipos definidos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Actualizando tipos de modificación...');
        
        try {
            // Ejecutar seeder de tipos de modificación
            if (class_exists('Database\Seeders\ModificationTypeSeeder')) {
                Artisan::call('db:seed', ['--class' => 'ModificationTypeSeeder']);
                $this->info('✅ Tipos de modificación actualizados correctamente');
                
                $this->newLine();
                $this->info('📋 Nuevos tipos disponibles:');
                $this->line('• Eliminar - Cualitativa');
                $this->line('• Eliminar - Cuantitativa');
                $this->line('• Agregar y/o Cambiar');
                $this->line('• Eliminar y/o Agregar');
                $this->line('• Agregar');
                $this->line('• Incremento de Presupuesto');
                $this->line('• Decremento de Presupuesto');
                $this->line('• Cambio de Especificaciones');
                $this->line('• Cambio de Proveedor');
                $this->line('• Cambio de Cantidad');
                $this->line('• Cambio de Fecha de Entrega');
                $this->line('• Otro');
                
                return 0;
            } else {
                $this->error('❌ Seeder de tipos de modificación no encontrado');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante la actualización: ' . $e->getMessage());
            return 1;
        }
    }
} 