<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeProjectRequest;
use App\Http\Resources\TypeProjectResource;
use App\Services\TypeProjectService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class TypeProjectController extends Controller
{
    use LogsActivity;

    protected $typeProjectService;

    public function __construct(TypeProjectService $typeProjectService)
    {
        $this->typeProjectService = $typeProjectService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->typeProjectService->getAllTypeProjectsByQuery($query, $perPage);

            return response()->json([
                'data' => TypeProjectResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de proyecto.'
            ], 500);
        }
    }

    public function store(TypeProjectRequest $request): JsonResponse
    {
        try {
            $typeProject = $this->typeProjectService->createTypeProject($request->validated());
            $this->logActivity('create_type_project', 'Usuario creó un tipo de proyecto con ID: ' . $typeProject->id);

            return response()->json([
                'message' => 'Tipo de proyecto ha sido guardado exitosamente',
                'data' => new TypeProjectResource($typeProject)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el tipo de proyecto. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $typeProject = $this->typeProjectService->getTypeProjectById($id);

            return response()->json([
                'data' => new TypeProjectResource($typeProject)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Tipo de proyecto no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, TypeProjectRequest $request): JsonResponse
    {
        try {
            $updated = $this->typeProjectService->updateTypeProject($id, $request->validated());
            $this->logActivity('update_type_project', 'Usuario actualizó el tipo de proyecto con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Tipo de proyecto ha sido actualizado exitosamente',
                'data' => new TypeProjectResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el tipo de proyecto. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->typeProjectService->deleteTypeProject($id);
            $this->logActivity('delete_type_project', 'Usuario eliminó el tipo de proyecto con ID: ' . $id);

            return response()->json([
                'message' => 'Tipo de proyecto ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el tipo de proyecto. ' . $e->getMessage()
            ], 500);
        }
    }
}
