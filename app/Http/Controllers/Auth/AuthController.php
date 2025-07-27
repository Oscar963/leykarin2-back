<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\SecurityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    protected $securityLogService;

    public function __construct(SecurityLogService $securityLogService)
    {
        $this->securityLogService = $securityLogService;
    }

    /**
     * Autentica al usuario e inicia una sesión.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('rut', 'password'), $request->boolean('remember'))) {
            $this->securityLogService->logFailedLogin($request->only('rut', 'password'), $request);
            return $this->sendFailedLoginResponse();
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (!$user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->securityLogService->logSuspendedAccountLogin($user, $request);
            return $this->sendSuspendedAccountResponse();
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        return $this->sendSuccessfulLoginResponse($user);
    }

    /**
     * Retorna los datos del usuario autenticado.
     * Esta ruta debe estar protegida por el middleware 'auth:sanctum'.
     */
    public function user(Request $request): UserResource
    {
        $user = $request->user()->load(['roles', 'permissions']);
        return new UserResource($user);
    }

    /**
     * Cierra la sesión del usuario.
     * Esta ruta debe estar protegida por el middleware 'auth:sanctum'.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->securityLogService->logLogout($request->user(), $request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Cerró sesión exitosamente']);
    }

    /**
     * Verifica si el usuario tiene una sesión activa.
     */
    public function isAuthenticated(): JsonResponse
    {
        return response()->json(['isAuthenticated' => Auth::check()]);
    }

    /**
     * Envía respuesta de inicio de sesión exitoso
     *
     * @param User $user
     * @return JsonResponse
     */
    protected function sendSuccessfulLoginResponse(User $user): JsonResponse
    {
        return response()->json([
            'message' => "Bienvenido(a) al sistema {$user->name} {$user->paternal_surname}",
            'user' => new UserResource($user->load(['roles', 'permissions']))
        ]);
    }

    /**
     * Envía respuesta de inicio de sesión fallido
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse(): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'error' => [
                'message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.'
            ]
        ], 401);
    }

    /**
     * Envía respuesta de cuenta suspendida
     *
     * @return JsonResponse
     */
    protected function sendSuspendedAccountResponse(): JsonResponse
    {
        return response()->json([
            'status' => 403,
            'error' => [
                'message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'
            ]
        ], 403);
    }
}
