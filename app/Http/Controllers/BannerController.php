<?php

namespace App\Http\Controllers;

use App\Http\Requests\BannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Services\BannerService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Exception;

class BannerController extends Controller
{
    use LogsActivity;

    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * Listar todos los banners.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q'); // Parámetro de búsqueda
            $perPage = $request->query('show');
            $banners = $this->bannerService->getAllBannersByQuery($query, $perPage);
            return response()->json(['data' =>  BannerResource::collection($banners)->response()->getData(true)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los banners.'], 500);
        }
    }

    /**
     * Guardar un nuevo banner.
     */
    public function store(BannerRequest $request): JsonResponse
    {
        try {
            $banner = $this->bannerService->createBanner($request->validated());
            $this->logActivity('create_banner', 'Usuario creó un banner con ID: ' . $banner->id);

            return response()->json(['message' => 'Banner guardado exitosamente', 'data' => new BannerResource($banner)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al guardar el banner.' . $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $banner = $this->bannerService->getBannerById($id);
            return response()->json(['data' => new BannerResource($banner)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Banner no encontrado.'], 404);
        }
    }
    /**
     * Actualizar un banner.
     */
    public function update(int $id, BannerRequest $request): JsonResponse
    {
        try {
            $updatedBanner = $this->bannerService->updateBanner($id, $request->validated());
            $this->logActivity('update_banner', 'Usuario actualizó el banner con ID: ' . $updatedBanner->id);

            return response()->json(['message' => 'Banner actualizado exitosamente', 'data' => new BannerResource($updatedBanner)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el banner.' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un banner.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bannerService->deleteBanner($id);
            $this->logActivity('delete_banner', 'Usuario eliminó el banner con ID: ' . $id);
            return response()->json(['message' => 'Banner eliminado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el banner.' . $e->getMessage()], 500);
        }
    }
}
