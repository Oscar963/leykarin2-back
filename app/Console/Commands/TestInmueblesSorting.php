<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inmueble;
use Illuminate\Http\Request;

class TestInmueblesSorting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:inmuebles-sorting {--sort_by=id} {--sort_order=desc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inmuebles sorting functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing inmuebles sorting functionality...');
        
        // Create a mock request
        $request = new Request();
        $request->merge([
            'sort_by' => $this->option('sort_by'),
            'sort_order' => $this->option('sort_order'),
            'page' => 1,
            'per_page' => 5
        ]);
        
        // Test the query
        $query = Inmueble::query();
        
        // Apply sorting manually (same logic as controller)
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort_order', 'desc'));
        
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        // Validate sort by field
        $allowedSortFields = ['id', 'numero', 'descripcion', 'calle', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        $inmuebles = $query->limit(5)->get();
        
        $this->info("Sorting by: {$sortBy} {$sortOrder}");
        $this->info("Total inmuebles found: " . Inmueble::count());
        $this->info("Showing first 5 results:");
        
        if ($inmuebles->isEmpty()) {
            $this->warn('No inmuebles found in database.');
            return 0;
        }
        
        $headers = ['ID', 'Número', 'Descripción', 'Calle', 'Created At'];
        $rows = [];
        
        foreach ($inmuebles as $inmueble) {
            $rows[] = [
                $inmueble->id,
                $inmueble->numero,
                substr($inmueble->descripcion, 0, 30) . '...',
                $inmueble->calle,
                $inmueble->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info('Sorting test completed successfully!');
        
        return 0;
    }
} 