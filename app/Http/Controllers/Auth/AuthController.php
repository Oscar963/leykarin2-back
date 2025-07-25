<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RutValidation;
use App\Services\SecurityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // Ensure we get the data correctly from JSON or form
        $data = $request->json()->all() ?: $request->all();
        
        if (empty($data)) {
            return response()->json([
                'status' => 422,
                'error' => [
                    'message' => 'No se recibieron datos. Verifique que está enviando JSON válido.'
                ]
            ], 422);
        }

        // Create a new request with the parsed data for validation
        $processedRequest = new Request($data);
        $processedRequest->setJson($request->json());

        $this->validateLogin($processedRequest);

        $credentials = [
            'rut' => $data['rut'] ?? null,
            'password' => $data['password'] ?? null
        ];
        
        $remember = $data['remember'] ?? false;

        if (!Auth::attempt($credentials, $remember)) {
            // Log intento fallido de login
            SecurityLogService::logFailedLogin($credentials, $request);
            return $this->sendFailedLoginResponse();
        }

        $user = Auth::user();

        if (!$user->status) {
            Auth::logout();
            // Log intento de login con cuenta suspendida
            SecurityLogService::logSuspendedAccountLogin($user, $request);
            return $this->sendSuspendedAccountResponse();
        }

        // Log login exitoso
        SecurityLogService::logSuccessfulLogin($user, $request);

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
            'status' => 401,
            'error' => [
                'message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.'
            ]
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
            'status' => 403,
            'error' => [
                'message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'
            ]
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
        // Para usar solo cookies, comenta esta línea:
        // $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => "Bienvenido(a) al sistema {$user->name} {$user->paternal_surname}",
            'user' => [
                'name' => $user->name,
                'email' => $user->email
            ]
            // Para usar solo cookies, elimina esta línea:
            // 'token' => $token
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Log logout
        if ($user) {
            SecurityLogService::logLogout($user, $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Cerró sesión exitosamente'
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/isAuthenticated",
     *      operationId="isAuthenticated",
     *      tags={"Autenticación"},
     *      summary="Verificar si el usuario está autenticado",
     *      description="Retorna true si el usuario está autenticado, false en caso contrario",
     *      @OA\Response(
     *          response=200,
     *          description="Estado de autenticación",
     *          @OA\JsonContent(
     *              @OA\Property(property="isAuthenticated", type="boolean", example=true)
     *          )
     *      )
     * )
     */
    public function isAuthenticated(): JsonResponse
    {
        return response()->json([
            'isAuthenticated' => Auth::check()
        ], 200);
    }

    public function user(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $user->load(['roles', 'permissions']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'paternal_surname' => $user->paternal_surname,
                'maternal_surname' => $user->maternal_surname,
                'rut' => $user->rut,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')
            ]
        ], 200);
    }

    public function permissions(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'rut' => $user->rut
            ],
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ], 200);
    }

    public function roles(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        return response()->json([
            'roles' => $user->getRoleNames()
        ], 200);
    }
}
