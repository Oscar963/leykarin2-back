<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\SecurityLogService;
use App\Helpers\RutHelper;
use App\Models\User;
use App\Traits\LogsActivity;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\TwoFactorRequiredException;
use App\Exceptions\InvalidTwoFactorCodeException;
use App\Exceptions\InvalidPasswordException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class AuthController extends Controller
{
    use LogsActivity;

    /**
     * Inyecta la instancia del servicio de log de seguridad.
     *
     * @param SecurityLogService $securityLogService
     */
    protected $securityLogService;

    public function __construct(SecurityLogService $securityLogService)
    {
        $this->securityLogService = $securityLogService;
    }

    /**
     * Maneja la solicitud de inicio de sesión del usuario.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'rut' => RutHelper::normalize($request->input('rut')),
            'password' => $request->input('password')
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->securityLogService->logFailedLogin(['rut' => $credentials['rut']], $request);
            return $this->sendFailedLoginResponse();
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (!$user->status) {
            $this->securityLogService->logSuspendedAccountLogin($user, $request);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return $this->sendSuspendedAccountResponse();
        }

        // Si el usuario tiene 2FA, generamos y enviamos el código por email
        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put('login.id', $user->getKey());
            $request->session()->put('login.remember', $request->boolean('remember'));
            Auth::guard('web')->logout();
            $request->session()->regenerate();

            // Generar y enviar código 2FA por email
            $user->generateTwoFactorCode();

            throw new TwoFactorRequiredException(null, $user->email);
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('login', 'Usuario inició sesión exitosamente.');

        return $this->sendSuccessfulAuthenticationResponse($request, $user);
    }

    /**
     * Alias para mantener compatibilidad con la ruta existente /two-factor-challenge.
     * Delegamos al método twoFactorAuthentication.
     */
    public function twoFactorChallenge(Request $request): JsonResponse
    {
        return $this->twoFactorAuthentication($request);
    }

    /**
     * Maneja el desafío de autenticación de dos factores (2FA) por email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function twoFactorAuthentication(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $this->getChallengedUser($request);

        if (!$user) {
            throw ValidationException::withMessages([
                'code' => ['Sesión de autenticación inválida. Por favor, inicia sesión nuevamente.']
            ]);
        }

        if (!$user->validateTwoFactorCode($request->input('code'))) {
            return $this->sendFailedTwoFactorResponse();
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('login', 'Usuario completó 2FA por email y inició sesión.');

        return $this->sendSuccessfulAuthenticationResponse($request, $user);
    }

    /**
     * Redirige al usuario al proveedor de Clave Única.
     *
     * @return RedirectResponse
     */
    public function redirectToClaveUnica(): RedirectResponse
    {
        return Socialite::driver('claveunica')->redirect();
    }

    /**
     * Obtiene la información del usuario desde Clave Única y lo autentica.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function handleClaveUnicaCallback(Request $request): RedirectResponse
    {
        try {
            $claveUnicaUser = Socialite::driver('claveunica')->user();
            $normalizedRut = RutHelper::normalize($claveUnicaUser->id);

            if (!$normalizedRut) {
                Log::error('Clave Única: RUT inválido recibido.', ['rut' => $claveUnicaUser->id]);
                return $this->redirectWithError('invalid_rut');
            }

            $user = User::updateOrCreate(
                ['rut' => $normalizedRut],
                [
                    'name' => data_get($claveUnicaUser->user, 'name.nombres.0', 'Usuario'),
                    'paternal_surname' => data_get($claveUnicaUser->user, 'name.apellidos.0', ''),
                    'maternal_surname' => data_get($claveUnicaUser->user, 'name.apellidos.1', ''),
                    'email' => $claveUnicaUser->email,
                    'status' => true,
                ]
            );

            if (!$user->status) {
                $this->securityLogService->logSuspendedAccountLogin($user, $request);
                return $this->redirectWithError('account_suspended');
            }

            Auth::login($user, true); // Iniciar sesión con "remember me" por defecto
            $request->session()->regenerate();

            $this->securityLogService->logSuccessfulLogin($user, $request);
            $this->logActivity('login', 'Usuario inició sesión con Clave Única.');

            return redirect(config('app.frontend_url') . '/dashboard?login=success');
        } catch (InvalidStateException $e) {
            Log::error('Clave Única: Estado inválido.', ['error' => $e->getMessage()]);
            $this->logActivity('login_failed', 'Intento de login con Clave Única falló (estado inválido).');
            return $this->redirectWithError('invalid_state');
        } catch (Throwable $e) {
            Log::error('Clave Única: Error general.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->logActivity('login_failed', 'Intento de login con Clave Única falló (error general).');
            throw $e; // Relanzar para que el handler de excepciones lo tome
        }
    }

    /**
     * Cierra la sesión activa del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->securityLogService->logLogout($request->user(), $request);
        $this->logActivity('logout', 'Usuario cerró sesión.');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Cerró sesión exitosamente.']);
    }

    /**
     * Retorna los datos del usuario autenticado.
     *
     * @param Request $request
     * @return AuthResource
     */
    public function user(Request $request): AuthResource
    {
        $user = $request->user()->load(['roles', 'permissions']);
        return new AuthResource($user);
    }

    /**
     * Prepara y envía la respuesta JSON para un inicio de sesión exitoso.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    protected function sendSuccessfulAuthenticationResponse(Request $request, User $user): JsonResponse
    {
        Auth::login($user, $request->session()->pull('login.remember', false));
        $request->session()->regenerate();

        return response()->json([
            'message' => "Bienvenido(a) al sistema {$user->name} {$user->paternal_surname}",
            'user' => new AuthResource($user->loadMissing(['roles', 'permissions']))
        ]);
    }

    /**
     * Recupera el usuario que está intentando el desafío 2FA.
     *
     * @param Request $request
     * @return User|null
     */
    protected function getChallengedUser(Request $request): ?User
    {
        if (!$userId = $request->session()->get('login.id')) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Construye una respuesta de error para un intento de login fallido.
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse(): JsonResponse
    {
        throw new InvalidCredentialsException('Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.');
    }

    /**
     * Construye una respuesta de error para un intento de 2FA fallido.
     *
     * @return JsonResponse
     */
    protected function sendFailedTwoFactorResponse(): JsonResponse
    {
        throw new InvalidTwoFactorCodeException();
    }

    /**
     * Construye una respuesta de error para un intento de login con cuenta suspendida.
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
     * Habilita la autenticación de dos factores por email para el usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        // Verificar contraseña actual por seguridad
        if (!Auth::guard('web')->validate(['rut' => $user->rut, 'password' => $request->password])) {
            throw new InvalidPasswordException();
        }

        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => 'La autenticación de dos factores ya está habilitada.'
            ], 400);
        }

        $user->enableTwoFactorAuthentication();
        $this->logActivity('two_factor_enabled', 'Usuario habilitó 2FA por email.');

        return response()->json([
            'message' => 'Autenticación de dos factores habilitada exitosamente.',
            'two_factor_enabled' => true
        ]);
    }

    /**
     * Deshabilita la autenticación de dos factores por email para el usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        // Verificar contraseña actual por seguridad
        if (!Auth::guard('web')->validate(['rut' => $user->rut, 'password' => $request->password])) {
            throw new InvalidPasswordException();
        }

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => 'La autenticación de dos factores ya está deshabilitada.'
            ], 400);
        }

        $user->disableTwoFactorAuthentication();
        $this->logActivity('two_factor_disabled', 'Usuario deshabilitó 2FA por email.');

        return response()->json([
            'message' => 'Autenticación de dos factores deshabilitada exitosamente.',
            'two_factor_enabled' => false
        ]);
    }

    /**
     * Obtiene el estado actual de 2FA del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTwoFactorStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'two_factor_enabled' => $user->two_factor_enabled,
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at
        ]);
    }

    /**
     * Reenvía el código 2FA por email durante el proceso de login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendTwoFactorCode(Request $request): JsonResponse
    {
        $user = $this->getChallengedUser($request);

        if (!$user) {
            return response()->json([
                'message' => 'Sesión de autenticación inválida. Por favor, inicia sesión nuevamente.'
            ], 401);
        }

        if (!$user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'message' => 'El usuario no tiene 2FA habilitado.'
            ], 400);
        }

        // Generar y enviar nuevo código
        $user->generateTwoFactorCode();
        $this->logActivity('two_factor_code_resent', 'Código 2FA reenviado por email.');

        throw new TwoFactorRequiredException(null, $user->email);
    }

    /**
     * Construye una URL de redirección con un parámetro de error para el frontend.
     *
     * @param string $errorCode
     * @return RedirectResponse
     */
    protected function redirectWithError(string $errorCode): RedirectResponse
    {
        return redirect(config('app.frontend_url') . '/login?error=' . $errorCode);
    }
}
