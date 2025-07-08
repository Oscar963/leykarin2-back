<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inmueble;
use Illuminate\Http\Request;

class DebugInmueblesSpecificFilters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:inmuebles-specific-filters {--numero=} {--descripcion=} {--calle=} {--q=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug inmuebles specific filters (numero, descripcion, calle)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $numero = $this->option('numero');
        $descripcion = $this->option('descripcion');
        $calle = $this->option('calle');
        $q = $this->option('q');

        $this->info("Debugging inmuebles specific filters");
        $this->info("numero: " . ($numero ?? 'null'));
        $this->info("descripcion: " . ($descripcion ?? 'null'));
        $this->info("calle: " . ($calle ?? 'null'));
        $this->info("q: " . ($q ?? 'null'));
        
        // Create a mock request
        $request = new Request();
        $request->merge([
            'numero' => $numero,
            'descripcion' => $descripcion,
            'calle' => $calle,
            'q' => $q,
            'page' => 1,
            'per_page' => 10
        ]);
        
        $this->info("\nRequest parameters:");
        $this->line("has('numero'): " . ($request->has('numero') ? 'true' : 'false'));
        $this->line("has('descripcion'): " . ($request->has('descripcion') ? 'true' : 'false'));
        $this->line("has('calle'): " . ($request->has('calle') ? 'true' : 'false'));
        $this->line("has('q'): " . ($request->has('q') ? 'true' : 'false'));
        
        // Test the query step by step
        $query = Inmueble::query();
        
        $this->info("\nTotal inmuebles before filters: " . $query->count());
        
        // Apply general search filter (q parameter)
        if ($request->has('q') && !empty($request->q)) {
            $this->info("Applying general search filter...");
            
            $query->where(function ($q) use ($request) {
                $q->where('numero', 'like', "%{$request->q}%")
                  ->orWhere('descripcion', 'like', "%{$request->q}%")
                  ->orWhere('calle', 'like', "%{$request->q}%")
                  ->orWhere('numeracion', 'like', "%{$request->q}%")
                  ->orWhere('lote_sitio', 'like', "%{$request->q}%")
                  ->orWhere('manzana', 'like', "%{$request->q}%")
                  ->orWhere('poblacion_villa', 'like', "%{$request->q}%")
                  ->orWhere('foja', 'like', "%{$request->q}%")
                  ->orWhere('inscripcion_numero', 'like', "%{$request->q}%")
                  ->orWhere('inscripcion_anio', 'like', "%{$request->q}%")
                  ->orWhere('rol_avaluo', 'like', "%{$request->q}%")
                  ->orWhere('superficie', 'like', "%{$request->q}%")
                  ->orWhere('deslinde_norte', 'like', "%{$request->q}%")
                  ->orWhere('deslinde_sur', 'like', "%{$request->q}%")
                  ->orWhere('deslinde_este', 'like', "%{$request->q}%")
                  ->orWhere('deslinde_oeste', 'like', "%{$request->q}%")
                  ->orWhere('decreto_incorporacion', 'like', "%{$request->q}%")
                  ->orWhere('decreto_destinacion', 'like', "%{$request->q}%")
                  ->orWhere('observaciones', 'like', "%{$request->q}%");
            });
            
            $this->info("Total inmuebles after general search: " . $query->count());
        }

        // Apply specific filters
        if ($request->has('numero')) {
            $this->info("Applying numero filter: " . $request->numero);
            $query->where('numero', 'like', '%' . $request->numero . '%');
            $this->info("Total inmuebles after numero filter: " . $query->count());
        }

        if ($request->has('descripcion')) {
            $this->info("Applying descripcion filter: " . $request->descripcion);
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
            $this->info("Total inmuebles after descripcion filter: " . $query->count());
        }

        if ($request->has('calle')) {
            $this->info("Applying calle filter: " . $request->calle);
            $query->where('calle', 'like', '%' . $request->calle . '%');
            $this->info("Total inmuebles after calle filter: " . $query->count());
        }
        
        // Apply sorting
        $query->orderBy('id', 'desc');
        
        // Get results
        $inmuebles = $query->limit(5)->get();
        
        if ($inmuebles->isEmpty()) {
            $this->warn("No inmuebles found with the specified filters.");
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