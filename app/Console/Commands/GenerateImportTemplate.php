<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsPurchaseTemplateExport;
use Illuminate\Support\Facades\Storage;

class GenerateImportTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:generate-template 
                            {--output= : Ruta de salida para el archivo (opcional)}
                            {--format=xlsx : Formato del archivo (xlsx, xls)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera una plantilla Excel para importar ítems de compra';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('🚀 Generando plantilla de importación para ítems de compra...');

            // Obtener opciones
            $outputPath = $this->option('output');
            $format = strtolower($this->option('format'));

            // Validar formato
            if (!in_array($format, ['xlsx', 'xls'])) {
                $this->error('❌ Formato no válido. Use xlsx o xls');
                return 1;
            }

            // Generar nombre de archivo
            $filename = $outputPath ?: "plantilla-items-compra.{$format}";

            // Si no se especifica ruta completa, usar storage/app/templates
            if (!pathinfo($filename, PATHINFO_DIRNAME) || pathinfo($filename, PATHINFO_DIRNAME) === '.') {
                $filename = storage_path("app/templates/{$filename}");
            }

            // Crear directorio si no existe
            $directory = dirname($filename);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->info("📁 Directorio creado: {$directory}");
            }

            // Generar plantilla
            $this->info('📊 Generando plantilla con datos de ejemplo...');

            // Usar Storage para evitar problemas de rutas
            $tempFile = Excel::raw(new ItemsPurchaseTemplateExport(), \Maatwebsite\Excel\Excel::XLSX);
            Storage::disk('local')->put("templates/plantilla-items-compra.{$format}", $tempFile);

            // Obtener la ruta completa del archivo guardado
            $filename = storage_path("app/templates/plantilla-items-compra.{$format}");

            // Verificar que el archivo se creó
            if (file_exists($filename)) {
                $fileSize = filesize($filename);
                $fileSizeFormatted = $this->formatBytes($fileSize);

                $this->info('✅ Plantilla generada exitosamente!');
                $this->info("📁 Ubicación: {$filename}");
                $this->info("📏 Tamaño: {$fileSizeFormatted}");

                // Mostrar información sobre el contenido
                $this->displayTemplateInfo();

                // Mostrar instrucciones de uso
                $this->displayUsageInstructions();

                return 0;
            } else {
                $this->error('❌ Error: No se pudo generar el archivo');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error al generar la plantilla: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Mostrar información sobre el contenido de la plantilla
     */
    private function displayTemplateInfo()
    {
        $this->newLine();
        $this->info('📋 Contenido de la plantilla:');
        $this->table(
            ['Hoja', 'Descripción', 'Contenido'],
            [
                ['Plantilla Ítems de Compra', 'Datos de ejemplo', '2 filas con ejemplos completos'],
                ['Asignaciones Presupuestarias', 'Referencias', 'Códigos y descripciones disponibles'],
                ['Tipos de Compra', 'Referencias', 'Tipos de compra válidos'],
                ['Meses de Publicación', 'Referencias', 'Meses disponibles para publicación'],
            ]
        );
    }

    /**
     * Mostrar instrucciones de uso
     */
    private function displayUsageInstructions()
    {
        $this->newLine();
        $this->info('📖 Instrucciones de uso:');
        $this->line('1. Abre el archivo Excel generado');
        $this->line('2. Ve a la hoja "Plantilla Ítems de Compra"');
        $this->line('3. Copia las filas de ejemplo y pégalas en tu archivo de trabajo');
        $this->line('4. Completa con tus datos siguiendo el formato de los ejemplos');
        $this->line('5. Usa las hojas de referencia para valores válidos');
        $this->line('6. Guarda tu archivo como .xlsx');
        $this->line('7. Importa usando el endpoint: POST /api/item-purchases/import/{projectId}');

        $this->newLine();
        $this->warn('⚠️  Campos obligatorios:');
        $this->line('   • Producto o Servicio');
        $this->line('   • Cantidad (mínimo 1)');
        $this->line('   • Monto (mínimo 0)');

        $this->newLine();
        $this->warn('⚠️  Formatos importantes:');
        $this->line('   • Mes de publicación: "Dic 2025"');
        $this->line('   • Asignación presupuestaria: "123456 - Descripción"');
        $this->line('   • Montos: Solo números (sin símbolos de moneda)');
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
