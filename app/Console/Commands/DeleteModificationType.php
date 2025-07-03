<?php

namespace App\Console\Commands;

use App\Models\ModificationType;
use Illuminate\Console\Command;

class DeleteModificationType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:delete-type {id : ID del tipo de modificación} {--force : Forzar eliminación sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina un tipo de modificación específico';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        try {
            // Buscar el tipo de modificación
            $modificationType = ModificationType::withCount('modifications')->find($id);
            
            if (!$modificationType) {
                $this->error("❌ No se encontró un tipo de modificación con ID: {$id}");
                return 1;
            }
            
            // Mostrar información del tipo
            $this->info("🗑️  Eliminando tipo de modificación:");
            $this->line("• ID: {$modificationType->id}");
            $this->line("• Nombre: {$modificationType->name}");
            $this->line("• Descripción: {$modificationType->description}");
            $this->line("• Modificaciones asociadas: {$modificationType->modifications_count}");
            $this->newLine();
            
            // Verificar si tiene modificaciones asociadas
            if ($modificationType->modifications_count > 0) {
                $this->warn("⚠️  ADVERTENCIA: Este tipo tiene {$modificationType->modifications_count} modificaciones asociadas.");
                $this->warn("   Eliminar este tipo puede causar problemas en las modificaciones existentes.");
                $this->newLine();
                
                if (!$this->confirm('¿Está seguro de que desea continuar? Esto puede afectar las modificaciones existentes.')) {
                    $this->info('Operación cancelada.');
                    return 0;
                }
            }
            
            // Confirmar eliminación
            if (!$this->option('force')) {
                if (!$this->confirm('¿Está seguro de que desea eliminar este tipo de modificación?')) {
                    $this->info('Operación cancelada.');
                    return 0;
                }
            }
            
            // Eliminar el tipo
            $name = $modificationType->name;
            $modificationType->delete();
            
            $this->info("✅ Tipo de modificación '{$name}' eliminado exitosamente.");
            
            // Mostrar tipos restantes
            $this->newLine();
            $this->info('📋 Tipos de modificación restantes:');
            $remainingTypes = ModificationType::orderBy('name')->get();
            
            if ($remainingTypes->isEmpty()) {
                $this->warn('⚠️  No quedan tipos de modificación.');
            } else {
                $this->table(
                    ['ID', 'Nombre', 'Descripción'],
                    $remainingTypes->map(function ($type) {
                        return [
                            $type->id,
                            $type->name,
                            substr($type->description, 0, 50) . (strlen($type->description) > 50 ? '...' : '')
                        ];
                    })
                );
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error al eliminar el tipo de modificación: ' . $e->getMessage());
            return 1;
        }
    }
} 