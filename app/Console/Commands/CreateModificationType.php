<?php

namespace App\Console\Commands;

use App\Models\ModificationType;
use Illuminate\Console\Command;

class CreateModificationType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:create-type {--name= : Nombre del tipo de modificación} {--description= : Descripción del tipo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un nuevo tipo de modificación de forma interactiva';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🆕 Creando nuevo tipo de modificación...');
        
        try {
            // Obtener nombre del tipo
            $name = $this->option('name');
            if (!$name) {
                $name = $this->ask('Ingrese el nombre del tipo de modificación');
            }
            
            // Validar que el nombre no esté vacío
            if (empty(trim($name))) {
                $this->error('❌ El nombre no puede estar vacío');
                return 1;
            }
            
            // Verificar si ya existe
            if (ModificationType::where('name', $name)->exists()) {
                $this->error("❌ Ya existe un tipo de modificación con el nombre: '{$name}'");
                return 1;
            }
            
            // Obtener descripción
            $description = $this->option('description');
            if (!$description) {
                $description = $this->ask('Ingrese la descripción del tipo de modificación');
            }
            
            // Validar que la descripción no esté vacía
            if (empty(trim($description))) {
                $this->error('❌ La descripción no puede estar vacía');
                return 1;
            }
            
            // Confirmar creación
            $this->newLine();
            $this->info('📋 Resumen del tipo de modificación:');
            $this->line("• Nombre: {$name}");
            $this->line("• Descripción: {$description}");
            $this->newLine();
            
            if (!$this->confirm('¿Desea crear este tipo de modificación?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
            
            // Crear el tipo de modificación
            $modificationType = ModificationType::create([
                'name' => $name,
                'description' => $description
            ]);
            
            $this->info("✅ Tipo de modificación '{$name}' creado exitosamente con ID: {$modificationType->id}");
            
            // Mostrar información adicional
            $this->newLine();
            $this->info('📊 Tipos de modificación existentes:');
            $types = ModificationType::orderBy('name')->get();
            $this->table(
                ['ID', 'Nombre', 'Descripción'],
                $types->map(function ($type) {
                    return [
                        $type->id,
                        $type->name,
                        substr($type->description, 0, 50) . (strlen($type->description) > 50 ? '...' : '')
                    ];
                })
            );
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error al crear el tipo de modificación: ' . $e->getMessage());
            return 1;
        }
    }
} 