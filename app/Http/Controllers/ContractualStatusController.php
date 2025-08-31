<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractualStatusRequest;
use App\Http\Resources\ContractualStatusResource;
use App\Services\ContractualStatusService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ContractualStatus;

class ContractualStatusController extends Controller
{
    use LogsActivity;

    private ContractualStatusService $contractualStatusService;

    public function __construct(ContractualStatusService $contractualStatusService)
    {
        $this->contractualStatusService = $contractualStatusService;
    }

    /**
     * Listar todos los estados contractuales.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $contractualStatuses = $this->contractualStatusService->getAllContractualStatusesByQuery($query, $perPage);

        return ContractualStatusResource::collection($contractualStatuses)->response();
    }

    /**
     * Guardar un nuevo estado contractual.
     * @param ContractualStatusRequest $request
     * @return JsonResponse
     */
    public function store(ContractualStatusRequest $request): JsonResponse
    {
        $contractualStatus = $this->contractualStatusService->createContractualStatus($request->validated());
        $this->logActivity('create_contractual_status', 'Usuario creó un estado contractual con ID: ' . $contractualStatus->id);
        return response()->json([
            'message' => 'Estado contractual guardado exitosamente',
            'data' => new ContractualStatusResource($contractualStatus)
        ], 201);
    }

    /**
     * Mostrar un estado contractual.
     * @param ContractualStatus $contractualStatus
     * @return JsonResponse
     */
    public function show(ContractualStatus $contractualStatus): JsonResponse
    {
        $this->logActivity('show_contractual_status', 'Usuario mostró un estado contractual con ID: ' . $contractualStatus->id);
        return response()->json([
            'data' => new ContractualStatusResource($contractualStatus)
        ], 200);
    }

    /**
     * Actualizar un estado contractual.
     * @param ContractualStatus $contractualStatus
     * @param ContractualStatusRequest $request
     * @return JsonResponse
     */
    public function update(ContractualStatus $contractualStatus, ContractualStatusRequest $request): JsonResponse
    {
        $updatedContractualStatus = $this->contractualStatusService->updateContractualStatus($contractualStatus, $request->validated());
        $this->logActivity('update_contractual_status', 'Usuario actualizó el estado contractual con ID: ' . $updatedContractualStatus->id);
        return response()->json([
            'message' => 'Estado contractual actualizado exitosamente',
            'data' => new ContractualStatusResource($updatedContractualStatus)
        ], 200);
    }

    /**
     * Eliminar un estado contractual.
     * @param ContractualStatus $contractualStatus
     * @return JsonResponse
     */
    public function destroy(ContractualStatus $contractualStatus): JsonResponse
    {
        $this->contractualStatusService->deleteContractualStatus($contractualStatus);
        $this->logActivity('delete_contractual_status', 'Usuario eliminó el estado contractual con ID: ' . $contractualStatus->id);
        return response()->json([
            'message' => 'Estado contractual eliminado exitosamente'
        ], 200);
    }
}
