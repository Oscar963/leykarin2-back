<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeDependencyRequest;
use App\Http\Resources\TypeDependencyResource;
use App\Models\TypeDependency;
use App\Services\TypeDependencyService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypeDependencyController extends Controller
{
    use LogsActivity;

    private TypeDependencyService $typeDependencyService;

    public function __construct(TypeDependencyService $typeDependencyService)
    {
        $this->typeDependencyService = $typeDependencyService;
    }

    /**
     * Listar todos los tipos de dependencias.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $typeDependencies = $this->typeDependencyService->getAllTypeDependenciesByQuery($query, $perPage);

        return TypeDependencyResource::collection($typeDependencies)->response();
    }

    /**
     * Guardar un nuevo tipo de dependencia.
     * @param TypeDependencyRequest $request
     * @return JsonResponse
     */
    public function store(TypeDependencyRequest $request): JsonResponse
    {
        $typeDependency = $this->typeDependencyService->createTypeDependency($request->validated());
        $this->logActivity('create_type_dependency', 'Usuario creó un tipo de dependencia con ID: ' . $typeDependency->id);
        return response()->json([
            'message' => 'Tipo de dependencia guardado exitosamente',
            'data' => new TypeDependencyResource($typeDependency)
        ], 201);
    }

    /**
     * Mostrar un tipo de dependencia.
     * @param TypeDependency $typeDependency
     * @return JsonResponse
     */
    public function show(TypeDependency $typeDependency): JsonResponse
    {
        $this->logActivity('show_type_dependency', 'Usuario mostró un tipo de dependencia con ID: ' . $typeDependency->id);
        return response()->json([
            'data' => new TypeDependencyResource($typeDependency)
        ], 200);
    }

    /**
     * Actualizar un tipo de dependencia.
     * @param TypeDependency $typeDependency
     * @param TypeDependencyRequest $request
     * @return JsonResponse
     */
    public function update(TypeDependency $typeDependency, TypeDependencyRequest $request): JsonResponse
    {
        $updatedTypeDependency = $this->typeDependencyService->updateTypeDependency($typeDependency, $request->validated());
        $this->logActivity('update_type_dependency', 'Usuario actualizó el tipo de dependencia con ID: ' . $updatedTypeDependency->id);
        return response()->json([
            'message' => 'Tipo de dependencia actualizado exitosamente',
            'data' => new TypeDependencyResource($updatedTypeDependency)
        ], 200);
    }

    /**
     * Eliminar un tipo de dependencia.
     * @param TypeDependency $typeDependency
     * @return JsonResponse
     */
    public function destroy(TypeDependency $typeDependency): JsonResponse
    {
        $this->typeDependencyService->deleteTypeDependency($typeDependency);    
        $this->logActivity('delete_type_dependency', 'Usuario eliminó el tipo de dependencia con ID: ' . $typeDependency->id);
        return response()->json([
            'message' => 'Tipo de dependencia eliminado exitosamente'
        ], 200);
    }
}
