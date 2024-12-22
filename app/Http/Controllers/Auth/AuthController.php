<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'rut' => ['required', 'string', new RutValidation()],
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        if (Auth::attempt($request->only('rut', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return response()->json(Auth::user(), 200);
        }

        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Cerró sesión exitosamente'], 200);
    }

    // Verificar usuario autenticado
    public function user(Request $request)
    {
        $user = User::find(Auth::id());
        return response()->json($request->user(), 200);
    }
}
