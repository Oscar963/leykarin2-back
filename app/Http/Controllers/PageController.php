<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Requests\PageRequest;
use App\Http\Resources\FileResource;
use App\Http\Resources\PageResource;
use App\Models\File;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Services\PageService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;

class PageController extends Controller
{
    use LogsActivity;

    protected $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Listar todas las páginas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');
            $banners = $this->pageService->getAllPagesByQuery($query, $perPage);
            return response()->json(['data' =>  PageResource::collection($banners)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener las páginas.'], 500);
        }
    }

    /**
     * Guardar una nueva página.
     */
    public function store(PageRequest $request): JsonResponse
    {
        try {
            $page = $this->pageService->createPage($request->validated());
            $this->logActivity('create_page', 'Usuario creó una página con ID: ' . $page->id);

            return response()->json(['message' => 'Página guardada exitosamente', 'data' => new PageResource($page)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar la página.' . $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $page = $this->pageService->getPageById($id);
            return response()->json(['data' => new PageResource($page)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Página no encontrada.'], 404);
        }
    }

    /**
     * Actualizar una página.
     */
    public function update(int $id, PageRequest $request): JsonResponse
    {
        try {
            $updatedPage = $this->pageService->updatePage($id, $request->validated());
            $this->logActivity('update_page', 'Usuario actualizó la página con ID: ' . $updatedPage->id);

            return response()->json(['message' => 'Página actualizada exitosamente', 'data' => new PageResource($updatedPage)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar la página.' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una página.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->pageService->deletePage($id);
            $this->logActivity('delete_page', 'Usuario eliminó la página con ID: ' . $id);
            return response()->json(['message' => 'Página eliminada exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar la página.' . $e->getMessage()], 500);
        }
    }

    /**
     * Listar todos los archivos.
     */
    public function indexFile(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');
            $idpage = $request->query('idpage');

            $files = $this->pageService->getAllFilesByQuery($idpage, $query, $perPage);
            return response()->json(['data' =>  FileResource::collection($files)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los los archivos.'], 500);
        }
    }

    /**
     * Guardar un nuevo archivo relacionado con una página.
     */
    public function uploadFile(FileRequest $request): JsonResponse
    {
        //return response()->json(['message' =>  $request->all()]);
        try {
            $file = $this->pageService->uploadFile($request->validated());
            $this->logActivity('upload_file', 'Usuario subió un archivo: ' . $file->name);

            return response()->json(['message' => 'Archivo subido exitosamente', 'data' => new FileResource($file)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al subir el archivo: ' . $e->getMessage()], 500);
        }
    }
}
