<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    /**
     * List all modules (dummy implementation, replace with real logic).
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Replace with real modules logic
        $modules = [
            ['id' => 1, 'name' => 'Módulo de Ejemplo', 'description' => 'Descripción de ejemplo'],
        ];
        return response()->json(['modules' => $modules], 200);
    }
}
