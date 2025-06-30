<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsPurchaseTemplateExport;

class GenerateItemsPurchaseExample extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:items-purchase-template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar plantilla optimizada de ítems de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando plantilla optimizada de ítems de compra...');

        try {
            $fileName = 'plantilla-items-compra-optimizada.xlsx';
            Excel::store(new ItemsPurchaseTemplateExport(), $fileName, 'public');

            $this->info("✓ Plantilla generada exitosamente: storage/app/public/{$fileName}");
            $this->line("  - 13 columnas (2 informativas)");
            $this->line("  - Sin validaciones complejas");
            $this->line("  - Hojas de referencia incluidas");
        } catch (\Exception $e) {
            $this->error("Error al generar la plantilla: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
