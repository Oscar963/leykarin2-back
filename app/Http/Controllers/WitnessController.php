<?php

namespace App\Http\Controllers;

use App\Http\Requests\WitnessRequest;
use App\Http\Resources\WitnessResource;
use App\Models\Witness;
use App\Services\WitnessService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WitnessController extends Controller
{
    use LogsActivity;

    private WitnessService $witnessService;

    public function __construct(WitnessService $witnessService)
    {
        $this->witnessService = $witnessService;
    }

    /**
     * Listar todos los testigos.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $witnesses = $this->witnessService->getAllWitnessesByQuery($query, $perPage);

        return WitnessResource::collection($witnesses)->response();
    }
    
    /**
     * Guardar un nuevo testigo.
     * @param WitnessRequest $request
     * @return JsonResponse
     */
    public function store(WitnessRequest $request): JsonResponse
    {
        $witness = $this->witnessService->createWitness($request->validated());
        $this->logActivity('create_witness', 'Usuario creó un testigo con ID: ' . $witness->id);
        return response()->json([
            'message' => 'Testigo guardado exitosamente',
            'data' => new WitnessResource($witness)
        ], 201);
    }

    /**
     * Mostrar un testigo.
     * @param Witness $witness
     * @return JsonResponse
     */
    public function show(Witness $witness): JsonResponse
    {
        $this->logActivity('show_witness', 'Usuario mostró un testigo con ID: ' . $witness->id);
        return response()->json([
            'data' => new WitnessResource($witness)
        ], 200);
    }

    /**
     * Actualizar un testigo.
     * @param Witness $witness
     * @param WitnessRequest $request
     * @return JsonResponse
     */
    public function update(Witness $witness, WitnessRequest $request): JsonResponse
    {
        $updatedWitness = $this->witnessService->updateWitness($witness, $request->validated());
        $this->logActivity('update_witness', 'Usuario actualizó el testigo con ID: ' . $updatedWitness->id);
        return response()->json([
            'message' => 'Testigo actualizado exitosamente',
            'data' => new WitnessResource($updatedWitness)
        ], 200);
    }

    /**
     * Eliminar un testigo.
     * @param Witness $witness
     * @return JsonResponse
     */
    public function destroy(Witness $witness): JsonResponse
    {
        $this->witnessService->deleteWitness($witness);
        $this->logActivity('delete_witness', 'Usuario eliminó el testigo con ID: ' . $witness->id);
        return response()->json([
            'message' => 'Testigo eliminado exitosamente'
        ], 200);
    }
}
