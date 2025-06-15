<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormF1Request;
use App\Http\Resources\FormF1Resource;
use App\Services\FormF1Service;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormF1Controller extends Controller
{
    use LogsActivity;

    protected $formF1Service;

    public function __construct(FormF1Service $formF1Service)
    {
        $this->formF1Service = $formF1Service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $perPage = $request->query('show');
            $results = $this->formF1Service->getAllFormF1sByQuery($query, $perPage);

            return response()->json([
                'data' => FormF1Resource::collection($results)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los formularios F1.'
            ], 500);
        }
    }

    public function store(FormF1Request $request): JsonResponse
    {
        try {
            $formF1 = $this->formF1Service->createFormF1($request->validated());
            $this->logActivity('create_form_f1', 'Usuario creó un formulario F1 con ID: ' . $formF1->id);

            return response()->json([
                'message' => 'Formulario F1 ha sido guardado exitosamente',
                'data' => new FormF1Resource($formF1)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el formulario F1. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $formF1 = $this->formF1Service->getFormF1ById($id);

            return response()->json([
                'data' => new FormF1Resource($formF1)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Formulario F1 no encontrado.'
            ], 404);
        }
    }

    public function update(int $id, FormF1Request $request): JsonResponse
    {
        try {
            $updated = $this->formF1Service->updateFormF1($id, $request->validated());
            $this->logActivity('update_form_f1', 'Usuario actualizó el formulario F1 con ID: ' . $updated->id);

            return response()->json([
                'message' => 'Formulario F1 ha sido actualizado exitosamente',
                'data' => new FormF1Resource($updated)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el formulario F1. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->formF1Service->deleteFormF1($id);
            $this->logActivity('delete_form_f1', 'Usuario eliminó el formulario F1 con ID: ' . $id);

            return response()->json([
                'message' => 'Formulario F1 ha sido eliminado exitosamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el formulario F1. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar un archivo por ID.
     */
    public function download(int $id): BinaryFileResponse
    {
        return $this->formF1Service->downloadFile($id);
    }
}
