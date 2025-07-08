<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inmueble;
use Illuminate\Http\Request;

class TestInmueblesGeneralSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:inmuebles-general-search {search_term} {--limit=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inmuebles general search functionality with q parameter';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $searchTerm = $this->argument('search_term');
        $limit = $this->option('limit');

        $this->info("Testing inmuebles general search with term: '{$searchTerm}'");
        
        // Create a mock request
        $request = new Request();
        $request->merge([
            'q' => $searchTerm,
            'page' => 1,
            'per_page' => $limit
        ]);
        
        // Test the query manually (same logic as controller)
        $query = Inmueble::query();
        
        // Apply general search
        $query->where(function ($q) use ($searchTerm) {
            $q->where('numero', 'like', "%{$searchTerm}%")
              ->orWhere('descripcion', 'like', "%{$searchTerm}%")
              ->orWhere('calle', 'like', "%{$searchTerm}%")
              ->orWhere('numeracion', 'like', "%{$searchTerm}%")
              ->orWhere('lote_sitio', 'like', "%{$searchTerm}%")
              ->orWhere('manzana', 'like', "%{$searchTerm}%")
              ->orWhere('poblacion_villa', 'like', "%{$searchTerm}%")
              ->orWhere('foja', 'like', "%{$searchTerm}%")
              ->orWhere('inscripcion_numero', 'like', "%{$searchTerm}%")
              ->orWhere('inscripcion_anio', 'like', "%{$searchTerm}%")
              ->orWhere('rol_avaluo', 'like', "%{$searchTerm}%")
              ->orWhere('superficie', 'like', "%{$searchTerm}%")
              ->orWhere('deslinde_norte', 'like', "%{$searchTerm}%")
              ->orWhere('deslinde_sur', 'like', "%{$searchTerm}%")
              ->orWhere('deslinde_este', 'like', "%{$searchTerm}%")
              ->orWhere('deslinde_oeste', 'like', "%{$searchTerm}%")
              ->orWhere('decreto_incorporacion', 'like', "%{$searchTerm}%")
              ->orWhere('decreto_destinacion', 'like', "%{$searchTerm}%")
              ->orWhere('observaciones', 'like', "%{$searchTerm}%");
        });
        
        // Apply sorting
        $query->orderBy('id', 'desc');
        
        $inmuebles = $query->limit($limit)->get();
        
        $this->info("Total inmuebles found: " . $inmuebles->count());
        $this->info("Showing first {$limit} results:");
        
        if ($inmuebles->isEmpty()) {
            $this->warn("No inmuebles found matching '{$searchTerm}'.");
            return 0;
        }
        
        $headers = ['ID', 'Número', 'Descripción', 'Calle', 'Manzana', 'Población'];
        $rows = [];
        
        foreach ($inmuebles as $inmueble) {
            $rows[] = [
                $inmueble->id,
                $inmueble->numero,
                substr($inmueble->descripcion, 0, 40) . '...',
                $inmueble->calle,
                $inmueble->manzana,
                $inmueble->poblacion_villa
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info('General search test completed successfully!');
        
        return 0;
    }
} 