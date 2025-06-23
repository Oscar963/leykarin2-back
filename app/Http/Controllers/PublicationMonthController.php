<?php

namespace App\Http\Controllers;

use App\Models\PublicationMonth;
use App\Services\PublicationMonthService;
use App\Http\Requests\PublicationMonthRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class PublicationMonthController extends Controller
{
    protected $publicationMonthService;

    public function __construct(PublicationMonthService $publicationMonthService)
    {
        $this->publicationMonthService = $publicationMonthService;
    }

    /**
     * Obtener todos los meses de publicación
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show', 15);

            if ($query) {
                $results = $this->publicationMonthService->getPublicationMonthsPaginated($perPage, $query);
            } else {
                $results = $this->publicationMonthService->getAllPublicationMonths();
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los meses de publicación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un mes específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $publicationMonth = $this->publicationMonthService->getPublicationMonthById($id);

            return response()->json([
                'success' => true,
                'data' => $publicationMonth
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Crear un nuevo mes de publicación
     */
    public function store(PublicationMonthRequest $request): JsonResponse
    {
        try {
            $publicationMonth = $this->publicationMonthService->createPublicationMonth($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mes de publicación creado exitosamente',
                'data' => $publicationMonth
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el mes de publicación: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Actualizar un mes de publicación
     */
    public function update(PublicationMonthRequest $request, int $id): JsonResponse
    {
        try {
            $publicationMonth = $this->publicationMonthService->updatePublicationMonth($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mes de publicación actualizado exitosamente',
                'data' => $publicationMonth
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mes de publicación: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Eliminar un mes de publicación
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->publicationMonthService->deletePublicationMonth($id);

            return response()->json([
                'success' => true,
                'message' => 'Mes de publicación eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mes de publicación: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener meses con formato para select
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year');
            $months = $this->publicationMonthService->getPublicationMonthsForSelect($year);

            return response()->json([
                'success' => true,
                'data' => $months
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los meses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener meses por año específico
     */
    public function getByYear(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year', date('Y'));
            $months = $this->publicationMonthService->getPublicationMonthsByYear($year);

            return response()->json([
                'success' => true,
                'data' => $months
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los meses por año: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener años disponibles
     */
    public function getAvailableYears(): JsonResponse
    {
        try {
            $years = $this->publicationMonthService->getAvailableYears();

            return response()->json([
                'success' => true,
                'data' => $years
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los años: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear meses para un año específico
     */
    public function createMonthsForYear(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2030',
            ]);

            $createdMonths = $this->publicationMonthService->createMonthsForYear($validated['year']);

            return response()->json([
                'success' => true,
                'message' => "Se crearon {$createdMonths->count()} meses para el año {$validated['year']}",
                'data' => $createdMonths
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear los meses: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener estadísticas de uso
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->publicationMonthService->getPublicationMonthsStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar meses de publicación
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term' => 'required|string|min:2',
                'limit' => 'integer|min:1|max:50'
            ]);

            $months = $this->publicationMonthService->searchPublicationMonths(
                $validated['term'],
                $validated['limit'] ?? 10
            );

            return response()->json([
                'success' => true,
                'data' => $months
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }
} 