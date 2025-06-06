<?php

namespace App\Http\Controllers;

use App\Models\StatusPurchasePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\StatusPurchasePlanResource;

class StatusPurchasePlanController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = StatusPurchasePlan::all();
        return response()->json([
            'data' => StatusPurchasePlanResource::collection($statuses)
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $status = StatusPurchasePlan::create($validated);
        return response()->json([
            'message' => 'Estado de plan de compra creado exitosamente',
            'data' => new StatusPurchasePlanResource($status)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $status = StatusPurchasePlan::findOrFail($id);
        return response()->json([
            'data' => new StatusPurchasePlanResource($status)
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $status = StatusPurchasePlan::findOrFail($id);
        $status->update($validated);
        return response()->json([
            'message' => 'Estado de plan de compra actualizado exitosamente',
            'data' => new StatusPurchasePlanResource($status)
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $status = StatusPurchasePlan::findOrFail($id);
        $status->delete();
        return response()->json([
            'message' => 'Estado de plan de compra eliminado exitosamente'
        ], 200);
    }
}
