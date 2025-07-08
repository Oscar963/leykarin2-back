<?php

namespace App\Http\Controllers;

use App\Models\Inmueble;
use App\Http\Requests\InmuebleRequest;
use App\Http\Resources\InmuebleResource;
use App\Http\Resources\InmuebleCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InmuebleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inmueble::query();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Sorting
        $sorting = $this->applySorting($query, $request);

        // General search filter (q parameter)
        if (
            $request->has('q') && !empty($request->q)
        ) {
            Log::info('Applying general search filter (index)', [
                'q' => $request->q,
                'has_q' => $request->has('q'),
                'empty_q' => empty($request->q)
            ]);
            $this->applyGeneralSearch($query, $request->q);
        } else {
            Log::info('No general search filter applied (index)', [
                'q' => $request->q ?? 'null',
                'has_q' => $request->has('q'),
                'empty_q' => empty($request->q ?? '')
            ]);
        }

        // Specific filters (these will work in combination with the general search)
        if ($request->has('numero')) {
            $query->where('numero', 'like', '%' . $request->numero . '%');
        }

        if ($request->has('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        if ($request->has('calle')) {
            $query->where('calle', 'like', '%' . $request->calle . '%');
        }

        // Cache results for 5 minutes
        $searchTerm = $request->q ?? '';
        $hasFilters = $request->has('q') || $request->has('numero') || $request->has('descripcion') || $request->has('calle');
        
        // If no filters, don't cache to ensure fresh data
        if (!$hasFilters) {
            $inmuebles = $query->paginate($perPage);
            Log::info('No filters applied, returning all data without cache', [
                'total_results' => $inmuebles->total(),
                'current_page' => $inmuebles->currentPage(),
                'per_page' => $inmuebles->perPage()
            ]);
        } else {
            $cacheKey = "inmuebles_page_{$page}_per_{$perPage}_sort_{$sorting['sort_by']}_{$sorting['sort_order']}_q_{$searchTerm}";
            $inmuebles = Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
                return $query->paginate($perPage);
            });
            Log::info('Filters applied, using cache', [
                'cache_key' => $cacheKey,
                'total_results' => $inmuebles->total(),
                'filters' => $request->only(['q', 'numero', 'descripcion', 'calle'])
            ]);
        }

        return response()->json([
            'data' => new InmuebleCollection($inmuebles),
            'meta' => [
                'current_page' => $inmuebles->currentPage(),
                'last_page' => $inmuebles->lastPage(),
                'per_page' => $inmuebles->perPage(),
                'total' => $inmuebles->total(),
                'from' => $inmuebles->firstItem(),
                'to' => $inmuebles->lastItem(),
                'sorting' => $sorting,
                'filters' => [
                    'general_search' => $request->q ?? null,
                    'specific_filters' => $request->only(['numero', 'descripcion', 'calle'])
                ]
            ],
            'links' => [
                'first' => $inmuebles->url(1),
                'last' => $inmuebles->url($inmuebles->lastPage()),
                'prev' => $inmuebles->previousPageUrl(),
                'next' => $inmuebles->nextPageUrl(),
            ]
        ], Response::HTTP_OK, [
            'Cache-Control' => 'public, max-age=300',
            'ETag' => md5($inmuebles->toJson())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InmuebleRequest $request
     * @return JsonResponse
     */
    public function store(InmuebleRequest $request): JsonResponse
    {
        $inmueble = Inmueble::create($request->validated());

        // Clear cache
        Cache::forget('inmuebles_*');

        return response()->json([
            'data' => new InmuebleResource($inmueble),
            'message' => 'Inmueble creado exitosamente'
        ], Response::HTTP_CREATED, [
            'Location' => url("/api/v1/inmuebles/{$inmueble->id}")
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Inmueble $inmueble
     * @return JsonResponse
     */
    public function show(Inmueble $inmueble): JsonResponse
    {
        return response()->json([
            'data' => new InmuebleResource($inmueble)
        ], Response::HTTP_OK, [
            'Cache-Control' => 'public, max-age=3600',
            'ETag' => md5($inmueble->toJson())
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param InmuebleRequest $request
     * @param Inmueble $inmueble
     * @return JsonResponse
     */
    public function update(InmuebleRequest $request, Inmueble $inmueble): JsonResponse
    {
        $inmueble->update($request->validated());

        // Clear cache
        Cache::forget('inmuebles_*');

        return response()->json([
            'data' => new InmuebleResource($inmueble),
            'message' => 'Inmueble actualizado exitosamente'
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Inmueble $inmueble
     * @return JsonResponse
     */
    public function destroy(Inmueble $inmueble): JsonResponse
    {
        $inmueble->delete();

        // Clear cache
        Cache::forget('inmuebles_*');

        return response()->json([
            'message' => 'Inmueble eliminado exitosamente'
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Bulk store multiple inmuebles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'inmuebles' => 'required|array|min:1|max:100',
            'inmuebles.*.numero' => 'required|string|max:255',
            'inmuebles.*.descripcion' => 'required|string',
            'inmuebles.*.calle' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $inmuebles = collect($request->inmuebles)->map(function ($data) {
                return Inmueble::create($data);
            });

            DB::commit();

            // Clear cache
            Cache::forget('inmuebles_*');

            return response()->json([
                'data' => InmuebleResource::collection($inmuebles),
                'message' => count($inmuebles) . ' inmuebles creados exitosamente'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => [
                    'code' => 'BULK_CREATE_ERROR',
                    'message' => 'Error al crear inmuebles en lote'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk update multiple inmuebles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'inmuebles' => 'required|array|min:1|max:100',
            'inmuebles.*.id' => 'required|exists:inmuebles,id',
            'inmuebles.*.numero' => 'sometimes|string|max:255',
            'inmuebles.*.descripcion' => 'sometimes|string',
            'inmuebles.*.calle' => 'sometimes|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $updated = 0;
            foreach ($request->inmuebles as $data) {
                $inmueble = Inmueble::find($data['id']);
                $inmueble->update(array_filter($data, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
                $updated++;
            }

            DB::commit();

            // Clear cache
            Cache::forget('inmuebles_*');

            return response()->json([
                'message' => $updated . ' inmuebles actualizados exitosamente'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => [
                    'code' => 'BULK_UPDATE_ERROR',
                    'message' => 'Error al actualizar inmuebles en lote'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete multiple inmuebles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'exists:inmuebles,id'
        ]);

        $deleted = Inmueble::whereIn('id', $request->ids)->delete();

        // Clear cache
        Cache::forget('inmuebles_*');

        return response()->json([
            'message' => $deleted . ' inmuebles eliminados exitosamente'
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Search inmuebles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $query = Inmueble::query();
        $searchTerm = $request->q;

        $this->applyGeneralSearch($query, $searchTerm);

        // Sorting for search results
        $sorting = $this->applySorting($query, $request);

        $inmuebles = $query->paginate(15);

        return response()->json([
            'data' => new InmuebleCollection($inmuebles),
            'meta' => [
                'search_term' => $searchTerm,
                'total_results' => $inmuebles->total(),
                'sorting' => $sorting
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Filter inmuebles with advanced filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        $query = Inmueble::query();

        // General search filter (q parameter)
        if ($request->has('q') && !empty($request->q)) {
            $this->applyGeneralSearch($query, $request->q);
        }

        // Apply specific filters
        if ($request->has('numero')) {
            $query->where('numero', 'like', '%' . $request->numero . '%');
        }

        if ($request->has('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        if ($request->has('calle')) {
            $query->where('calle', 'like', '%' . $request->calle . '%');
        }

        if ($request->has('created_at_from')) {
            $query->whereDate('created_at', '>=', $request->created_at_from);
        }

        if ($request->has('created_at_to')) {
            $query->whereDate('created_at', '<=', $request->created_at_to);
        }

        // Sorting for filtered results
        $sorting = $this->applySorting($query, $request);

        $inmuebles = $query->paginate(15);

        return response()->json([
            'data' => new InmuebleCollection($inmuebles),
            'meta' => [
                'filters_applied' => [
                    'general_search' => $request->q ?? null,
                    'specific_filters' => $request->only(['numero', 'descripcion', 'calle', 'created_at_from', 'created_at_to'])
                ],
                'total_results' => $inmuebles->total(),
                'sorting' => $sorting
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Get inmuebles statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = Cache::remember('inmuebles_statistics', 3600, function () {
            return [
                'total' => Inmueble::count(),
                'created_today' => Inmueble::whereDate('created_at', today())->count(),
                'created_this_week' => Inmueble::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'created_this_month' => Inmueble::whereMonth('created_at', now()->month)->count(),
                'updated_today' => Inmueble::whereDate('updated_at', today())->count(),
            ];
        });

        return response()->json([
            'data' => $stats
        ], Response::HTTP_OK, [
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    /**
     * Export inmuebles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'xlsx');
        $inmuebles = Inmueble::all();

        // In a real implementation, you would generate and return the file
        return response()->json([
            'message' => 'Exportación iniciada',
            'data' => [
                'format' => $format,
                'total_records' => $inmuebles->count(),
                'download_url' => url("/api/v1/inmuebles/export/download?format={$format}")
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Custom export with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customExport(Request $request): JsonResponse
    {
        $request->validate([
            'filters' => 'array',
            'columns' => 'array',
            'format' => 'in:xlsx,csv,pdf'
        ]);

        // Implementation for custom export
        return response()->json([
            'message' => 'Exportación personalizada iniciada',
            'data' => [
                'filters_applied' => $request->filters,
                'columns_selected' => $request->columns,
                'format' => $request->format
            ]
        ], Response::HTTP_OK);
    }

        /**
     * Apply sorting to a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return array
     */
    private function applySorting($query, Request $request): array
    {
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
        
        return [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'allowed_sort_fields' => $allowedSortFields,
            'allowed_sort_orders' => ['asc', 'desc']
        ];
    }

    /**
     * Apply general search filter to a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return void
     */
    private function applyGeneralSearch($query, string $searchTerm): void
    {
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
    }
}
