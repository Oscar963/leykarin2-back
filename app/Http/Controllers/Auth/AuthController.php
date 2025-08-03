<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\SecurityLogService;
use App\Helpers\RutHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Traits\LogsActivity;

class AuthController extends Controller
{
    use LogsActivity;
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
        $rut = $request->input('rut');
        $rut = RutHelper::normalize($rut); // Normaliza aquí

        if (!Auth::attempt(['rut' => $rut, 'password' => $request->input('password')], $request->boolean('remember'))) {
            $this->securityLogService->logFailedLogin(['rut' => $rut], $request);
            return $this->sendFailedLoginResponse();
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (!$user->status) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->securityLogService->logSuspendedAccountLogin($user, $request);
            return $this->sendSuspendedAccountResponse();
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('login', 'Usuario inició sesión');
        return $this->sendSuccessfulLoginResponse($user);
    }

    /**
     * Redirige al usuario a la página de autenticación de Clave Única.
     */
    public function redirectToClaveUnica(): RedirectResponse
    {
        return Socialite::driver('claveunica')->redirect();
    }

    /**
     * Maneja la respuesta de Clave Única después de la autenticación.
     */
    public function handleClaveUnicaCallback(Request $request)
    {
        try {
            $claveUnicaUser = Socialite::driver('claveunica')->user();

            // Log de la respuesta para debugging (remover en producción)
            Log::info('Clave Única Response', [
                'user_data' => $claveUnicaUser->user ?? null,
                'id' => $claveUnicaUser->id ?? null,
                'email' => $claveUnicaUser->email ?? null
            ]);

            // Validar y normalizar el RUT
            $rawRut = $claveUnicaUser->id; // El provider devuelve el RUN en el campo 'id'

            if (empty($rawRut)) {
                Log::error('Clave Única: RUT vacío recibido');
                return redirect(config('app.frontend_url') . '/login?error=invalid_rut');
            }

            $normalizedRut = RutHelper::normalize($rawRut);

            if (!$normalizedRut) {
                Log::error('Clave Única: RUT inválido', ['rut' => $rawRut]);
                return redirect(config('app.frontend_url') . '/login?error=invalid_rut');
            }

            // Extraer datos del usuario de forma segura
            $userData = $claveUnicaUser->user ?? [];
            $nombres = $userData['name']['nombres'] ?? [];
            $apellidos = $userData['name']['apellidos'] ?? [];

            $firstName = !empty($nombres) ? $nombres[0] : '';
            $paternalSurname = !empty($apellidos) ? $apellidos[0] : '';
            $maternalSurname = isset($apellidos[1]) ? $apellidos[1] : '';

            // Buscar o crear el usuario en el sistema
            $user = User::firstOrCreate(
                ['rut' => $normalizedRut],
                [
                    'name' => $firstName,
                    'paternal_surname' => $paternalSurname,
                    'maternal_surname' => $maternalSurname,
                    'email' => $claveUnicaUser->email ?? '',
                    'status' => true, // Activar el usuario por defecto
                ]
            );

            // Si el usuario ya existía, actualizar sus datos con la información más reciente
            if (!$user->wasRecentlyCreated) {
                $user->update([
                    'name' => $firstName ?: $user->name,
                    'paternal_surname' => $paternalSurname ?: $user->paternal_surname,
                    'maternal_surname' => $maternalSurname ?: $user->maternal_surname,
                    'email' => $claveUnicaUser->email ?: $user->email,
                ]);
            }

            // Verificar si la cuenta está activa en nuestro sistema
            if (!$user->status) {
                $this->securityLogService->logSuspendedAccountLogin($user, $request);
                return redirect(config('app.frontend_url') . '/login?error=account_suspended');
            }

            // Iniciar sesión para el usuario
            Auth::login($user);
            $request->session()->regenerate();
            $this->securityLogService->logSuccessfulLogin($user, $request);

            // Redirigir al usuario al dashboard
            $this->logActivity('login', 'Usuario inició sesión con Clave Única exitosamente');
            return redirect(config('app.frontend_url') . '/dashboard?login=success');
        } catch (InvalidStateException $e) {
            Log::error('Clave Única: Estado inválido', ['error' => $e->getMessage()]);
            $this->logActivity('login', 'Usuario inició sesión con Clave Única fallido');
            return redirect(config('app.frontend_url') . '/login?error=invalid_state');
        } catch (\Exception $e) {
            Log::error('Clave Única: Error general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->logActivity('login', 'Usuario inició sesión con Clave Única fallido');
            return redirect(config('app.frontend_url') . '/login?error=claveunica_failed');
        }
    }

    /**
     * Retorna los datos del usuario autenticado.
     * Esta ruta debe estar protegida por el middleware 'auth:sanctum'.
     */
    public function user(Request $request): AuthResource
    {
        $user = $request->user()->load(['roles', 'permissions']);
        return new AuthResource($user);
    }

    /**
     * Cierra la sesión del usuario.
     * Esta ruta debe estar protegida por el middleware 'auth:sanctum'.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->securityLogService->logLogout($request->user(), $request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->logActivity('logout', 'Usuario cerró sesión');
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
            'user' => new AuthResource($user->load(['roles', 'permissions']))
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
