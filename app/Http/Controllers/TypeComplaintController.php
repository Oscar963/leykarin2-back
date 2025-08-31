<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeComplaintRequest;
use App\Http\Resources\TypeComplaintResource;
use App\Models\TypeComplaint;
use App\Services\TypeComplaintService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypeComplaintController extends Controller
{
    use LogsActivity;

    private TypeComplaintService $typeComplaintService;

    public function __construct(TypeComplaintService $typeComplaintService)
    {
        $this->typeComplaintService = $typeComplaintService;
    }

    /**
     * Listar todos los tipos de denuncias.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $typeComplaints = $this->typeComplaintService->getAllTypeComplaintsByQuery($query, $perPage);

        return TypeComplaintResource::collection($typeComplaints)->response();
    }

    /**
     * Guardar un nuevo tipo de denuncia.
     * @param TypeComplaintRequest $request
     * @return JsonResponse
     */
    public function store(TypeComplaintRequest $request): JsonResponse
    {
        $typeComplaint = $this->typeComplaintService->createTypeComplaint($request->validated());
        $this->logActivity('create_type_complaint', 'Usuario creó un tipo de denuncia con ID: ' . $typeComplaint->id);
        return response()->json([
            'message' => 'Tipo de denuncia guardado exitosamente',
            'data' => new TypeComplaintResource($typeComplaint)
        ], 201);
    }

    /**
     * Mostrar un tipo de denuncia.
     * @param TypeComplaint $typeComplaint
     * @return JsonResponse
     */
    public function show(TypeComplaint $typeComplaint): JsonResponse
    {
        $this->logActivity('show_type_complaint', 'Usuario mostró un tipo de denuncia con ID: ' . $typeComplaint->id);
        return response()->json([
            'data' => new TypeComplaintResource($typeComplaint)
        ], 200);
    }

    /**
     * Actualizar un tipo de denuncia.
     * @param TypeComplaint $typeComplaint
     * @param TypeComplaintRequest $request
     * @return JsonResponse
     */
    public function update(TypeComplaint $typeComplaint, TypeComplaintRequest $request): JsonResponse
    {
        $updatedTypeComplaint = $this->typeComplaintService->updateTypeComplaint($typeComplaint, $request->validated());
        $this->logActivity('update_type_complaint', 'Usuario actualizó el tipo de denuncia con ID: ' . $updatedTypeComplaint->id);
        return response()->json([
            'message' => 'Tipo de denuncia actualizado exitosamente',
            'data' => new TypeComplaintResource($updatedTypeComplaint)
        ], 200);
    }

    /**
     * Eliminar un tipo de denuncia.
     * @param TypeComplaint $typeComplaint
     * @return JsonResponse
     */
    public function destroy(TypeComplaint $typeComplaint): JsonResponse
    {
        $this->typeComplaintService->deleteTypeComplaint($typeComplaint);
        $this->logActivity('delete_type_complaint', 'Usuario eliminó el tipo de denuncia con ID: ' . $typeComplaint->id);
        return response()->json([
            'message' => 'Tipo de denuncia eliminado exitosamente'
        ], 200);
    }
}
