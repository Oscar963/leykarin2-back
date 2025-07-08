<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inmueble;
use Illuminate\Http\Request;

class TestInmueblesNoFilters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:inmuebles-no-filters {--page=1} {--per_page=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test inmuebles endpoint without filters';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $page = $this->option('page');
        $perPage = $this->option('per_page');

        $this->info("Testing inmuebles endpoint without filters");
        $this->info("Page: {$page}, Per Page: {$perPage}");
        
        // Create a mock request without filters
        $request = new Request();
        $request->merge([
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        // Test the query manually (same logic as controller)
        $query = Inmueble::query();
        
        // Apply sorting
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
        
        $this->info("Total inmuebles in database: " . Inmueble::count());
        $this->info("Query without filters should return all data");
        
        $inmuebles = $query->paginate($perPage);
        
        $this->info("Results:");
        $this->line("Total results: " . $inmuebles->total());
        $this->line("Current page: " . $inmuebles->currentPage());
        $this->line("Per page: " . $inmuebles->perPage());
        $this->line("Last page: " . $inmuebles->lastPage());
        $this->line("Showing items: " . $inmuebles->firstItem() . " to " . $inmuebles->lastItem());
        
        if ($inmuebles->isEmpty()) {
            $this->warn('No inmuebles found in database.');
            return 0;
        }
        
        $headers = ['ID', 'Número', 'Descripción', 'Calle'];
        $rows = [];
        
        foreach ($inmuebles as $inmueble) {
            $rows[] = [
                $inmueble->id,
                $inmueble->numero,
                substr($inmueble->descripcion, 0, 40) . '...',
                $inmueble->calle
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info('Test completed successfully!');
        $this->info('When no filters are applied, all data should be returned.');
        
        return 0;
    }
} 