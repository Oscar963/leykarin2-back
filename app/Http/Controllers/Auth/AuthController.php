<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RutValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Autentica a un usuario en el sistema
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->validateLogin($request);

        $credentials = $request->only('rut', 'password');
        $remember = $request->boolean('remember', false);

        if (!Auth::attempt($credentials, $remember)) {
            return $this->sendFailedLoginResponse();
        }

        $user = Auth::user();

        if (!$user->status) {
            Auth::logout();
            return $this->sendSuspendedAccountResponse();
        }

        return $this->sendSuccessfulLoginResponse($user);
    }

    /**
     * Valida los datos de inicio de sesión
     *
     * @param Request $request
     * @throws ValidationException
     */
    protected function validateLogin(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'rut' => ['required', 'string', new RutValidation()],
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Envía respuesta de error por credenciales incorrectas
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.'
        ], 401);
    }

    /**
     * Envía respuesta de error por cuenta suspendida
     *
     * @return JsonResponse
     */
    protected function sendSuspendedAccountResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'
        ], 403);
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
            'message' => 'Bienvenido(a) al sistema ' . $user->name,
            'user' => [
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 200);
    }

    /**
     * Cierra la sesión del usuario
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Cerró sesión exitosamente'
        ], 200);
    }

    /**
     * Verifica si el usuario está autenticado
     *
     * @return JsonResponse
     */
    public function isAuthenticated(): JsonResponse
    {
        return response()->json([
            'isAuthenticated' => Auth::check()
        ], 200);
    }

    /**
     * Obtiene la información del usuario autenticado
     *
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        $user = User::with('directions')->find(Auth::id());

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'paternal_surname' => $user->paternal_surname,
                'maternal_surname' => $user->maternal_surname,
                'rut' => $user->rut,
                'email' => $user->email,
                'status' => $user->status,
                'direction' => $user->directions->first()->name,
            ]
        ], 200);
    }

    /**
     * Obtiene los permisos del usuario autenticado
     *
     * @return JsonResponse
     */
    public function permissions(): JsonResponse
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

    /**
     * Obtiene los roles del usuario autenticado
     *
     * @return JsonResponse
     */
    public function roles(): JsonResponse
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
