<?php

namespace App\Console\Commands;

use App\Models\ItemPurchase;
use App\Models\PublicationMonth;
use Illuminate\Console\Command;

class TestPublicationMonthRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:publication-month-relation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la relación entre ItemPurchase y PublicationMonth';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Prueba de Relación PublicationMonth ===');

        // Mostrar todos los meses disponibles
        $this->info('1. Meses disponibles:');
        $months = PublicationMonth::active()->get();
        foreach ($months as $month) {
            $this->line("   - {$month->formatted_date} ({$month->name}) - Número: {$month->month_number}");
        }

        // Mostrar meses por año
        $this->info('2. Meses por año:');
        $years = PublicationMonth::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        foreach ($years as $year) {
            $this->line("   Año {$year}:");
            $yearMonths = PublicationMonth::byYear($year)->orderBy('month_number')->get();
            foreach ($yearMonths as $month) {
                $this->line("     - {$month->short_name} ({$month->name})");
            }
        }

        // Mostrar algunos ítems de compra con sus meses de publicación
        $this->info('3. Ítems de compra con meses de publicación:');
        $items = ItemPurchase::with('publicationMonth')->take(5)->get();
        
        if ($items->isEmpty()) {
            $this->warn('   No hay ítems de compra en la base de datos.');
        } else {
            foreach ($items as $item) {
                $publicationDate = $item->publication_date_formatted ?? 'No asignado';
                $this->line("   - Ítem {$item->id}: {$item->product_service} | Mes: {$publicationDate}");
            }
        }

        // Probar el accessor
        $this->info('4. Prueba del accessor publication_date_formatted:');
        $testItem = ItemPurchase::with('publicationMonth')->first();
        if ($testItem) {
            $this->line("   - Ítem de prueba: {$testItem->id}");
            $this->line("   - publication_month_id: {$testItem->publication_month_id}");
            $this->line("   - publication_date_formatted: {$testItem->publication_date_formatted}");
            
            if ($testItem->publicationMonth) {
                $this->line("   - Relación cargada: {$testItem->publicationMonth->formatted_date}");
            }
        }

        $this->info('=== Prueba completada ===');
    }
} 