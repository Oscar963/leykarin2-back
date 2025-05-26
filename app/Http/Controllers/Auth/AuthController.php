<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RutValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => ['required', 'string', new RutValidation()],
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('rut', 'password');
        $remember = $request->boolean('remember', false);

        if (!Auth::attempt($credentials, $remember)) {
            return response()->json(['message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.'], 401);
        }

        $user = Auth::user();
        if (!$user->status) {
            Auth::logout();
            return response()->json(['message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'], 403);
        }

        return response()->json([
            'message' => 'Bienvenido(a) al sistema ' . $user->name
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Cerró sesión exitosamente'], 200);
    }

    public function isAuthenticated()
    {
        return response()->json(['isAuthenticated' => Auth::check()], 200);
    }

    public function user()
    {
        $user = User::with('direction')->find(Auth::id());

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'paternal_surname' => $user->paternal_surname,
            'maternal_surname' => $user->maternal_surname,
            'rut' => $user->rut,
            'email' => $user->email,
            'status' => $user->status,
            'direction' => $user->direction->name,

        ], 200);
    }

    public function permissions(Request $request)
    {
        $user = User::find(Auth::id());

        return response()->json([
            'user' => [
                'name' => $user->name,
                'paternal_surname' => $user->paternal_surname,
                'maternal_surname' => $user->maternal_surname,
                'email' => $user->email,
                'rut' => $user->rut
            ],
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }

    public function roles(Request $request)
    {
        $user = User::find(Auth::id());

        return response()->json([
            'user' => [
                'name' => $user->name,
                'paternal_surname' => $user->paternal_surname,
                'maternal_surname' => $user->maternal_surname,
                'email' => $user->email,
                'rut' => $user->rut
            ],
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }
}
