<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnexoResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\MobileResource;
use App\Services\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\FileService;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebController extends Controller
{
    protected $webService;
    protected $fileService;

    public function __construct(WebService $webService, FileService $fileService)
    {
        $this->webService = $webService;
        $this->fileService = $fileService;
    }

    /**
     * Obtener todos los banners.
     */
    public function getAllBanners(): JsonResponse
    {
        try {
            $banners = $this->webService->getAllBanners();
            return response()->json(['data' => $banners], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los banners: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener un banner por ID.
     */
    public function getBannerById(int $id): JsonResponse
    {
        try {
            $banner = $this->webService->getBannerById($id);
            return response()->json(['data' => $banner], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener el banner: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Obtener todos los popups.
     */
    public function getAllPopups(): JsonResponse
    {
        try {
            $popups = $this->webService->getAllPopups();
            return response()->json(['data' => $popups], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los popups: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener un popup por ID.
     */
    public function getPopupById(int $id): JsonResponse
    {
        try {
            $popup = $this->webService->getPopupById($id);
            return response()->json(['data' => $popup], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener el popup: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Obtener todas las páginas.
     */
    public function getAllPages(): JsonResponse
    {
        try {
            $pages = $this->webService->getAllPages();
            return response()->json(['data' => $pages], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las páginas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener una página por ID.
     */
    public function getPageById(int $id): JsonResponse
    {
        try {
            $page = $this->webService->getPageById($id);
            return response()->json(['data' => $page], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener la página: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Obtener una página por Slug.
     */
    public function getPageSlug(string $slug): JsonResponse
    {
        try {
            $page = $this->webService->getPageBySlug($slug);
            return response()->json(['data' => $page], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener la página: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Buscar archivos por nombre o descripción.
     */
    public function searchFiles(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');

            if (!$query) {
                return response()->json(['message' => 'El parámetro de búsqueda es requerido.'], 400);
            }

            $files = $this->webService->searchFiles($query);

            return response()->json(['data' => FileResource::collection($files)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al buscar archivos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descargar un archivo por ID.
     */
    public function downloadFile(int $id): BinaryFileResponse
    {
        $this->logActivity('download_file', 'Usuario descargo el archivo con ID: ' . $id);
        return $this->fileService->downloadFile($id);
    }

    /**
     * Buscar anexos por nombre, numero o descripción.
     */
    public function searchAnexos(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show', 50); // Valor por defecto: 15

            $anexos = $this->webService->searchAnexos($query, $perPage);

            return response()->json(['data' => AnexoResource::collection($anexos)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al buscar anexos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar mobiles por nombre, numero o descripción.
     */
    public function searchMobiles(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show', 15); // Valor por defecto: 15

            $mobiles = $this->webService->searchMobiles($query, $perPage);

            return response()->json(['data' => MobileResource::collection($mobiles)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al buscar mobiles: ' . $e->getMessage()], 500);
        }
    }
}
