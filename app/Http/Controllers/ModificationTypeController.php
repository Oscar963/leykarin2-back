<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModificationTypeRequest;
use App\Http\Resources\ModificationTypeResource;
use App\Models\ModificationType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModificationTypeController extends Controller
{
    /**
     * Obtiene todos los tipos de modificación con paginación y filtrado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query');
        $perPage = $request->get('per_page', 15);

        $modificationTypes = ModificationType::with('modifications')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ModificationTypeResource::collection($modificationTypes),
            'pagination' => [
                'current_page' => $modificationTypes->currentPage(),
                'last_page' => $modificationTypes->lastPage(),
                'per_page' => $modificationTypes->perPage(),
                'total' => $modificationTypes->total(),
            ]
        ]);
    }

    /**
     * Obtiene un tipo de modificación específico
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $modificationType = ModificationType::with(['modifications' => function ($query) {
            $query->with(['purchasePlan.direction', 'createdBy', 'approvedBy']);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new ModificationTypeResource($modificationType)
        ]);
    }

    /**
     * Crea un nuevo tipo de modificación
     *
     * @param ModificationTypeRequest $request
     * @return JsonResponse
     */
    public function store(ModificationTypeRequest $request): JsonResponse
    {
        $modificationType = ModificationType::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de modificación creado exitosamente',
            'data' => new ModificationTypeResource($modificationType)
        ], 201);
    }

    /**
     * Actualiza un tipo de modificación existente
     *
     * @param ModificationTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ModificationTypeRequest $request, int $id): JsonResponse
    {
        $modificationType = ModificationType::findOrFail($id);
        $modificationType->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de modificación actualizado exitosamente',
            'data' => new ModificationTypeResource($modificationType)
        ]);
    }

    /**
     * Elimina un tipo de modificación
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $modificationType = ModificationType::findOrFail($id);

        // Verificar si el tipo está siendo usado
        if ($modificationType->isInUse()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el tipo de modificación porque está siendo usado por modificaciones existentes'
            ], 422);
        }

        $modificationType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de modificación eliminado exitosamente'
        ]);
    }

    /**
     * Obtiene estadísticas de un tipo de modificación
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getStatistics(int $id): JsonResponse
    {
        $modificationType = ModificationType::findOrFail($id);
        $statistics = $modificationType->getStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'modification_type' => new ModificationTypeResource($modificationType),
                'statistics' => $statistics
            ]
        ]);
    }
} 