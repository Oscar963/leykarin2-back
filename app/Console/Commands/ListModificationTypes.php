<?php

namespace App\Console\Commands;

use App\Models\ModificationType;
use Illuminate\Console\Command;

class ListModificationTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modifications:list-types {--detailed : Mostrar información detallada} {--count : Mostrar solo el conteo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos los tipos de modificación existentes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $types = ModificationType::withCount('modifications')->orderBy('name')->get();
            
            if ($types->isEmpty()) {
                $this->warn('⚠️  No hay tipos de modificación registrados.');
                $this->info('💡 Puede crear tipos usando: php artisan modifications:create-type');
                return 0;
            }
            
            if ($this->option('count')) {
                $this->info("📊 Total de tipos de modificación: {$types->count()}");
                return 0;
            }
            
            $this->info("📋 Tipos de modificación ({$types->count()} total):");
            $this->newLine();
            
            if ($this->option('detailed')) {
                // Mostrar información detallada
                $this->table(
                    ['ID', 'Nombre', 'Descripción', 'Modificaciones', 'Creado'],
                    $types->map(function ($type) {
                        return [
                            $type->id,
                            $type->name,
                            $type->description,
                            $type->modifications_count,
                            $type->created_at->format('d/m/Y H:i')
                        ];
                    })
                );
            } else {
                // Mostrar información básica
                $this->table(
                    ['ID', 'Nombre', 'Modificaciones'],
                    $types->map(function ($type) {
                        return [
                            $type->id,
                            $type->name,
                            $type->modifications_count
                        ];
                    })
                );
            }
            
            // Mostrar estadísticas
            $this->newLine();
            $this->info('📊 Estadísticas:');
            $totalModifications = $types->sum('modifications_count');
            $this->line("• Total de modificaciones: {$totalModifications}");
            $this->line("• Tipos más usados:");
            
            $topTypes = $types->sortByDesc('modifications_count')->take(3);
            foreach ($topTypes as $type) {
                if ($type->modifications_count > 0) {
                    $this->line("  - {$type->name}: {$type->modifications_count} modificaciones");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error al listar tipos de modificación: ' . $e->getMessage());
            return 1;
        }
    }
} 