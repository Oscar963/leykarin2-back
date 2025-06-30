<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsPurchaseSampleExport;

class GenerateSampleImportFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:generate-sample {--rows=10 : Número de filas de ejemplo a generar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un archivo Excel de ejemplo con datos para importar ítems de compra (una sola hoja)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rows = (int) $this->option('rows');

        if ($rows < 1 || $rows > 100) {
            $this->error('El número de filas debe estar entre 1 y 100.');
            return 1;
        }

        $this->info("Generando archivo de ejemplo con {$rows} filas...");

        try {
            $fileName = 'ejemplo-importacion-items-compra-' . date('Y-m-d-H-i-s') . '.xlsx';
            $filePath = storage_path('app/' . $fileName);

            Excel::store(new ItemsPurchaseSampleExport($rows), $fileName);

            $this->info("✅ Archivo generado exitosamente:");
            $this->line("📁 Ubicación: {$filePath}");
            $this->line("📊 Filas generadas: {$rows}");
            $this->line("");
            $this->info("Este archivo contiene:");
            $this->line("• Una sola hoja con datos de ítems de compra");
            $this->line("• Encabezados en el orden correcto");
            $this->line("• Datos de ejemplo realistas");
            $this->line("• Formato listo para importar");
            $this->line("");
            $this->warn("⚠️  IMPORTANTE: Este archivo tiene UNA SOLA HOJA para evitar problemas de importación.");
            $this->warn("   Si necesitas las hojas de referencia, usa el comando: php artisan import:generate-template");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error al generar el archivo: ' . $e->getMessage());
            return 1;
        }
    }
}
