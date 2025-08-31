<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplainantRequest;
use App\Http\Resources\ComplainantResource;
use App\Models\Complainant;
use App\Services\ComplainantService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplainantController extends Controller
{
    use LogsActivity;

    private ComplainantService $complainantService;

    public function __construct(ComplainantService $complainantService)
    {
        $this->complainantService = $complainantService;
    }

    /**
     * Listar todos los denunciantes.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $complainants = $this->complainantService->getAllComplainantsByQuery($query, $perPage);

        return ComplainantResource::collection($complainants)->response();
    }

    /**
     * Guardar un nuevo denunciante.
     * @param ComplainantRequest $request
     * @return JsonResponse
     */
    public function store(ComplainantRequest $request): JsonResponse
    {
        $complainant = $this->complainantService->createComplainant($request->validated());
        $this->logActivity('create_complainant', 'Usuario creó un denunciante con ID: ' . $complainant->id);
        return response()->json([
            'message' => 'Denunciante guardado exitosamente',
            'data' => new ComplainantResource($complainant)
        ], 201);
    }

    /**
     * Mostrar un denunciante.
     * @param Complainant $complainant
     * @return JsonResponse
     */
    public function show(Complainant $complainant): JsonResponse
    {
        $this->logActivity('show_complainant', 'Usuario mostró un denunciante con ID: ' . $complainant->id);
        return response()->json([
            'data' => new ComplainantResource($complainant)
        ], 200);
    }

    /**
     * Actualizar un denunciante.
     * @param Complainant $complainant
     * @param ComplainantRequest $request
     * @return JsonResponse
     */
    public function update(Complainant $complainant, ComplainantRequest $request): JsonResponse
    {
        $updatedComplainant = $this->complainantService->updateComplainant($complainant, $request->validated());
        $this->logActivity('update_complainant', 'Usuario actualizó el denunciante con ID: ' . $updatedComplainant->id);
        return response()->json([
            'message' => 'Denunciante actualizado exitosamente',
            'data' => new ComplainantResource($updatedComplainant)
        ], 200);
    }

    /**
     * Eliminar un denunciante.
     * @param Complainant $complainant
     * @return JsonResponse
     */
    public function destroy(Complainant $complainant): JsonResponse
    {   
        $this->complainantService->deleteComplainant($complainant);    
        $this->logActivity('delete_complainant', 'Usuario eliminó el denunciante con ID: ' . $complainant->id);
        return response()->json([
            'message' => 'Denunciante eliminado exitosamente'
        ], 200);
    }
}
