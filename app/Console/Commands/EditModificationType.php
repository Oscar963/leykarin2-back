<?php

namespace App\Console\Commands;

use App\Models\ModificationType;
use Illuminate\Console\Command;

class EditModificationType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:edit-type {id : ID del tipo de modificación} {--name= : Nuevo nombre} {--description= : Nueva descripción}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edita un tipo de modificación existente';

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
            $modificationType = ModificationType::find($id);
            
            if (!$modificationType) {
                $this->error("❌ No se encontró un tipo de modificación con ID: {$id}");
                return 1;
            }
            
            // Mostrar información actual
            $this->info("✏️  Editando tipo de modificación:");
            $this->line("• ID: {$modificationType->id}");
            $this->line("• Nombre actual: {$modificationType->name}");
            $this->line("• Descripción actual: {$modificationType->description}");
            $this->newLine();
            
            // Obtener nuevo nombre
            $newName = $this->option('name');
            if (!$newName) {
                $newName = $this->ask('Ingrese el nuevo nombre (deje vacío para mantener el actual)', $modificationType->name);
            }
            
            // Validar que el nombre no esté vacío
            if (empty(trim($newName))) {
                $this->error('❌ El nombre no puede estar vacío');
                return 1;
            }
            
            // Verificar si el nuevo nombre ya existe (excluyendo el actual)
            if ($newName !== $modificationType->name && ModificationType::where('name', $newName)->exists()) {
                $this->error("❌ Ya existe un tipo de modificación con el nombre: '{$newName}'");
                return 1;
            }
            
            // Obtener nueva descripción
            $newDescription = $this->option('description');
            if (!$newDescription) {
                $newDescription = $this->ask('Ingrese la nueva descripción (deje vacío para mantener la actual)', $modificationType->description);
            }
            
            // Validar que la descripción no esté vacía
            if (empty(trim($newDescription))) {
                $this->error('❌ La descripción no puede estar vacía');
                return 1;
            }
            
            // Mostrar cambios
            $this->newLine();
            $this->info('📋 Cambios a realizar:');
            if ($newName !== $modificationType->name) {
                $this->line("• Nombre: '{$modificationType->name}' → '{$newName}'");
            }
            if ($newDescription !== $modificationType->description) {
                $this->line("• Descripción: '{$modificationType->description}' → '{$newDescription}'");
            }
            if ($newName === $modificationType->name && $newDescription === $modificationType->description) {
                $this->warn('⚠️  No hay cambios que realizar.');
                return 0;
            }
            $this->newLine();
            
            // Confirmar cambios
            if (!$this->confirm('¿Desea aplicar estos cambios?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
            
            // Actualizar el tipo
            $modificationType->update([
                'name' => $newName,
                'description' => $newDescription
            ]);
            
            $this->info("✅ Tipo de modificación actualizado exitosamente.");
            $this->newLine();
            $this->info('📋 Información actualizada:');
            $this->line("• ID: {$modificationType->id}");
            $this->line("• Nombre: {$modificationType->name}");
            $this->line("• Descripción: {$modificationType->description}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error al editar el tipo de modificación: ' . $e->getMessage());
            return 1;
        }
    }
} 