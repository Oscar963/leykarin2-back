<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use App\Services\ActivityLogService;
use App\Http\Resources\ActivityLogResource;

class ActivityLogController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Listar todos los logs de actividad.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $perPage = $request->query('per_page');
        $activityLogs = $this->activityLogService->getAllActivityLogsByQuery($query, $perPage);

        $metadata = [];

        return ActivityLogResource::collection($activityLogs)->additional(['meta' => $metadata])->response();
    }
}
