<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
use App\Services\GoogleAuthService;
use App\Services\SecurityLogService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class GoogleLoginController extends Controller
{
    use LogsActivity;

    protected GoogleAuthService $googleAuthService;
    protected SecurityLogService $securityLogService;

    public function __construct(
        GoogleAuthService $googleAuthService,
        SecurityLogService $securityLogService
    ) {
        $this->googleAuthService = $googleAuthService;
        $this->securityLogService = $securityLogService;
    }

    /**
     * Maneja el login con Google OAuth usando ID Token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Verificar que Google OAuth esté habilitado
            if (!$this->googleAuthService->isEnabled()) {
                return response()->json([
                    'message' => 'Autenticación con Google no está disponible.'
                ], 503);
            }

            // Validar la recepción del ID Token
            $request->validate([
                'id_token' => 'required|string',
            ]);

            $idToken = $request->input('id_token');

            // Verificar y validar el ID Token de Google
            $payload = $this->googleAuthService->verifyIdToken($idToken);

            // Validar dominio corporativo si está configurado
            $this->googleAuthService->validateCorporateDomain($payload);

            // Buscar o crear usuario
            $user = $this->googleAuthService->findOrCreateUser($payload);

            // Autenticar usuario usando sesión (cookies)
            $authData = $this->googleAuthService->authenticateUser($user, $request);

            // Respuesta exitosa (sin token, usa cookies)
            return response()->json([
                'message' => "Bienvenido(a) al sistema {$user->name} {$user->paternal_surname}",
                'user' => new AuthResource($authData['user']),
                'auth_provider' => 'google',
                'auth_method' => 'session'
            ], 200);
        } catch (AuthorizationException $e) {
            // Errores de autorización (dominio, usuario no registrado, cuenta suspendida)
            $this->logActivity('google_login_failed', 'Intento de login con Google falló: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (ValidationException $e) {
            // Errores de validación
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Errores generales
            Log::error('Error general en Google OAuth login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_ip' => $request->ip()
            ]);

            $this->logActivity('google_login_error', 'Error técnico en login con Google: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor. Por favor intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Obtiene la configuración de Google OAuth para el frontend.
     *
     * @return JsonResponse
     */
    public function config(): JsonResponse
    {
        try {
            $config = $this->googleAuthService->getClientConfig();

            return response()->json([
                'google_oauth' => $config
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo configuración de Google OAuth', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error obteniendo configuración.'
            ], 500);
        }
    }

    /**
     * Desvincula la cuenta de Google del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unlink(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isGoogleUser()) {
                return response()->json([
                    'message' => 'Esta cuenta no está vinculada con Google.'
                ], 400);
            }

            // Verificar que el usuario tenga contraseña para login tradicional
            if (!$user->canUseTraditionalAuth()) {
                return response()->json([
                    'message' => 'No se puede desvincular Google. Configure una contraseña primero.'
                ], 400);
            }

            // Limpiar datos de Google
            $user->update([
                'google_id' => null,
                'google_email' => null,
                'google_name' => null,
                'google_avatar' => null,
                'google_verified_at' => null,
                'google_domain' => null,
                'auth_provider' => 'local'
            ]);

            $this->logActivity('google_unlink', 'Usuario desvinculó su cuenta de Google.');

            Log::info('Cuenta de Google desvinculada', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'Cuenta de Google desvinculada exitosamente.',
                'user' => new AuthResource($user->fresh()->load(['roles', 'permissions']))
            ]);
        } catch (\Exception $e) {
            Log::error('Error desvinculando cuenta de Google', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Vincula una cuenta existente con Google OAuth.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function link(Request $request): JsonResponse
    {
        try {
            // Verificar que Google OAuth esté habilitado
            if (!$this->googleAuthService->isEnabled()) {
                return response()->json([
                    'message' => 'Autenticación con Google no está disponible.'
                ], 503);
            }

            $user = $request->user();

            if ($user->isGoogleUser()) {
                return response()->json([
                    'message' => 'Esta cuenta ya está vinculada con Google.'
                ], 400);
            }

            // Validar la recepción del ID Token
            $request->validate([
                'id_token' => 'required|string',
            ]);

            $idToken = $request->input('id_token');

            // Verificar y validar el ID Token de Google
            $payload = $this->googleAuthService->verifyIdToken($idToken);

            // Validar dominio corporativo si está configurado
            $this->googleAuthService->validateCorporateDomain($payload);

            // Verificar que el email de Google coincida con el del usuario
            if ($payload['email'] !== $user->email) {
                return response()->json([
                    'message' => 'El email de Google debe coincidir con el email de tu cuenta.'
                ], 400);
            }

            // Verificar que no exista otro usuario con este Google ID
            $existingUser = User::findByGoogleId($payload['sub']);
            if ($existingUser && $existingUser->id !== $user->id) {
                return response()->json([
                    'message' => 'Esta cuenta de Google ya está vinculada a otro usuario.'
                ], 409);
            }

            // Vincular cuenta
            $user->updateGoogleInfo($payload);

            $this->logActivity('google_link', 'Usuario vinculó su cuenta con Google.');

            Log::info('Cuenta vinculada con Google', [
                'user_id' => $user->id,
                'email' => $user->email,
                'google_id' => $payload['sub']
            ]);

            return response()->json([
                'message' => 'Cuenta vinculada con Google exitosamente.',
                'user' => new AuthResource($user->fresh()->load(['roles', 'permissions']))
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error vinculando cuenta con Google', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Obtiene el estado de vinculación con Google del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'is_google_linked' => $user->isGoogleUser(),
                'google_email' => $user->google_email,
                'google_name' => $user->google_name,
                'google_avatar' => $user->google_avatar,
                'google_verified_at' => $user->google_verified_at,
                'auth_provider' => $user->auth_provider,
                'can_unlink' => $user->canUseTraditionalAuth()
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado de Google OAuth', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }
}
