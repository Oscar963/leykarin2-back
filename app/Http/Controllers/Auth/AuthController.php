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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
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

        // Si el usuario tiene 2FA, preparamos la sesión para el siguiente paso.
        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put('login.id', $user->getKey());
            $request->session()->put('login.remember', $request->boolean('remember'));
            Auth::guard('web')->logout();
            $request->session()->regenerate();
            abort(423, 'Two-Factor authentication required.'); // Locked
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('login', 'Usuario inició sesión exitosamente.');

        return $this->sendSuccessfulAuthenticationResponse($request, $user);
    }

    /**
     * Maneja el desafío de autenticación de dos factores (2FA).
     *
     * @param TwoFactorLoginRequest $request
     * @return JsonResponse
     */
    public function twoFactorChallenge(TwoFactorLoginRequest $request): JsonResponse
    {
        $user = $this->getChallengedUser($request);

        if (!$user || !$this->isValidTwoFactorCode($request, $user)) {
            // El Rate Limiter se aplica automáticamente a través de la configuración de Fortify.
            return $this->sendFailedTwoFactorResponse();
        }

        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('login', 'Usuario completó 2FA y inició sesión.');

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
     * Valida el código 2FA o un código de recuperación.
     *
     * @param Request $request
     * @param User $user
     * @return bool
     */
    protected function isValidTwoFactorCode(Request $request, User $user): bool
    {
        $provider = app(TwoFactorAuthenticationProvider::class);
        $code = $request->input('code');

        // Validar código TOTP
        if ($provider->verify(decrypt($user->two_factor_secret), $code)) {
            return true;
        }



        return false;
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
        return response()->json([
            'message' => 'El código de autenticación de dos factores proporcionado no es válido.',
            'errors' => [
                'code' => ['El código proporcionado es incorrecto.'],
            ]
        ], 422);
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
