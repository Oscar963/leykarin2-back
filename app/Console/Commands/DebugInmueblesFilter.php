<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inmueble;
use Illuminate\Http\Request;

class DebugInmueblesFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:inmuebles-filter {search_term}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug inmuebles filter functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $searchTerm = $this->argument('search_term');

        $this->info("Debugging inmuebles filter with term: '{$searchTerm}'");
        
        // Create a mock request
        $request = new Request();
        $request->merge([
            'q' => $searchTerm,
            'page' => 1,
            'per_page' => 10
        ]);
        
        $this->info("Request parameters:");
        $this->line("q: " . ($request->q ?? 'null'));
        $this->line("has('q'): " . ($request->has('q') ? 'true' : 'false'));
        $this->line("!empty(q): " . (!empty($request->q) ? 'true' : 'false'));
        
        // Test the query step by step
        $query = Inmueble::query();
        
        $this->info("\nTotal inmuebles before filter: " . $query->count());
        
        // Apply general search
        if ($request->has('q') && !empty($request->q)) {
            $this->info("Applying general search filter...");
            
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
            
            $this->info("Total inmuebles after filter: " . $query->count());
            
            // Show the SQL query
            $this->info("SQL Query: " . $query->toSql());
            $this->info("SQL Bindings: " . json_encode($query->getBindings()));
            
        } else {
            $this->warn("Filter condition not met!");
        }
        
        // Get results
        $inmuebles = $query->limit(5)->get();
        
        if ($inmuebles->isEmpty()) {
            $this->warn("No inmuebles found matching '{$searchTerm}'.");
            
            // Let's check if the term exists in any field
            $this->info("\nChecking if term exists in any field...");
            $anyMatch = Inmueble::where('descripcion', 'like', "%{$searchTerm}%")->count();
            $this->line("Matches in descripcion: {$anyMatch}");
            
            $anyMatch = Inmueble::where('numero', 'like', "%{$searchTerm}%")->count();
            $this->line("Matches in numero: {$anyMatch}");
            
            $anyMatch = Inmueble::where('calle', 'like', "%{$searchTerm}%")->count();
            $this->line("Matches in calle: {$anyMatch}");
            
        } else {
            $this->info("Found {$inmuebles->count()} inmuebles:");
            
            $headers = ['ID', 'Número', 'Descripción', 'Calle'];
            $rows = [];
            
            foreach ($inmuebles as $inmueble) {
                $rows[] = [
                    $inmueble->id,
                    $inmueble->numero,
                    substr($inmueble->descripcion, 0, 50) . '...',
                    $inmueble->calle
                ];
            }
            
            $this->table($headers, $rows);
        }
        
        return 0;
    }
} 