<?php

namespace App\Http\Controllers;

use App\Models\StatusItemPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\StatusItemPurchaseResource;

class StatusItemPurchaseController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = StatusItemPurchase::all();
        return response()->json([
            'data' => StatusItemPurchaseResource::collection($statuses)
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $status = StatusItemPurchase::create($validated);
        return response()->json([
            'message' => 'Estado de ítem de compra creado exitosamente',
            'data' => new StatusItemPurchaseResource($status)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $status = StatusItemPurchase::findOrFail($id);
        return response()->json([
            'data' => new StatusItemPurchaseResource($status)
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $status = StatusItemPurchase::findOrFail($id);
        $status->update($validated);
        return response()->json([
            'message' => 'Estado de ítem de compra actualizado exitosamente',
            'data' => new StatusItemPurchaseResource($status)
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $status = StatusItemPurchase::findOrFail($id);
        $status->delete();
        return response()->json([
            'message' => 'Estado de ítem de compra eliminado exitosamente'
        ], 200);
    }
}
