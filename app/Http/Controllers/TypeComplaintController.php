<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeComplaintRequest;
use App\Http\Resources\TypeComplaintResource;
use App\Services\TypeComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;


class TypeComplaintController extends Controller
{
    use LogsActivity;

    protected $typeComplaintService;

    public function __construct(TypeComplaintService $typeComplaintService)
    {
        $this->typeComplaintService = $typeComplaintService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->typeComplaintService->getAllTypeComplaintsByQuery($query, $perPage);

            return response()->json([
                'data' => TypeComplaintResource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de denuncia.'
            ], 500);
        }
    }

    public function store(TypeComplaintRequest $request): JsonResponse
    {
        try {
            $typeComplaint = $this->typeComplaintService->createTypeComplaint($request->validated());
            $this->logActivity('create_type_complaint', 'Usuario creó un tipo de denuncia con ID: ' . $typeComplaint->id);

            return response()->json([
                'message' => 'Tipo de denuncia ha sido guardado exitosamente',
                'data' => new TypeComplaintResource($typeComplaint)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el tipo de denuncia. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $typeComplaint = $this->typeComplaintService->getTypeComplaintById($id);

            return response()->json([
                'data' => new TypeComplaintResource($typeComplaint)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Tipo de denuncia no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, TypeComplaintRequest $request): JsonResponse
    {
        try {
            $updated = $this->typeComplaintService->updateTypeComplaint($id, $request->validated());
            $this->logActivity('update_type_complaint', 'Usuario actualizó el tipo de denuncia con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Tipo de denuncia ha sido actualizado exitosamente',
                'data' => new TypeComplaintResource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el tipo de denuncia. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->typeComplaintService->deleteTypeComplaint($id);
            $this->logActivity('delete_type_complaint', 'Usuario eliminó el tipo de denuncia con ID: ' . $id);

            return response()->json([
                'message' => 'Tipo de denuncia ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el tipo de denuncia. ' . $e->getMessage()
            ], 500);
        }
    }
}
