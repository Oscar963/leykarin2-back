<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RutValidation;
use App\Services\SecurityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API Sistema de Planes de Compra Municipal",
 *      description="API RESTful para gestión de planes de compra municipal con roles, permisos y metas medibles",
 *      @OA\Contact(
 *          email="admin@plancompras.cl"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor API"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="apiKey",
 *      in="header",
 *      name="Authorization",
 *      description="Ingrese su token en el formato: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints para autenticación y gestión de sesiones"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="login",
     *      tags={"Autenticación"},
     *      summary="Iniciar sesión en el sistema",
     *      description="Autentica a un usuario usando RUT y contraseña",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="rut", type="string", example="12345678-9", description="RUT del usuario"),
     *                  @OA\Property(property="password", type="string", example="password123", description="Contraseña del usuario"),
     *                  @OA\Property(property="remember", type="boolean", example=false, description="Recordar sesión")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login exitoso",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Bienvenido(a) al sistema Juan Pérez"),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="name", type="string", example="Juan"),
     *                  @OA\Property(property="email", type="string", example="juan@example.com")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Credenciales incorrectas",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="integer", example=401),
     *              @OA\Property(property="error", type="object",
     *                  @OA\Property(property="message", type="string", example="Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Cuenta suspendida",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="integer", example=403),
     *              @OA\Property(property="error", type="object",
     *                  @OA\Property(property="message", type="string", example="Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Datos de validación incorrectos",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/logout",
     *      operationId="logout",
     *      tags={"Autenticación"},
     *      summary="Cerrar sesión en el sistema",
     *      description="Cierra la sesión del usuario autenticado",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Logout exitoso",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Cerró sesión exitosamente")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autenticado",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      )
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/user",
     *      operationId="user",
     *      tags={"Autenticación"},
     *      summary="Obtener información del usuario autenticado",
     *      description="Retorna la información completa del usuario autenticado",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Información del usuario",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Juan"),
     *                  @OA\Property(property="rut", type="string", example="12345678-9"),
     *                  @OA\Property(property="email", type="string", example="juan@example.com"),
     *                  @OA\Property(property="status", type="boolean", example=true),
     *                  @OA\Property(property="direction", type="object"),
     *                  @OA\Property(property="direction_id", type="integer", example=1),
     *                  @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                  @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autenticado",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      )
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/permissions",
     *      operationId="permissions",
     *      tags={"Autenticación"},
     *      summary="Obtener permisos del usuario autenticado",
     *      description="Retorna los roles y permisos del usuario autenticado",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Permisos del usuario",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="name", type="string", example="Juan"),
     *                  @OA\Property(property="email", type="string", example="juan@example.com"),
     *                  @OA\Property(property="rut", type="string", example="12345678-9")
     *              ),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autenticado",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      )
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/roles",
     *      operationId="roles",
     *      tags={"Autenticación"},
     *      summary="Obtener roles del usuario autenticado",
     *      description="Retorna los roles del usuario autenticado",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Roles del usuario",
     *          @OA\JsonContent(
     *              @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autenticado",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      )
     * )
     */
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
