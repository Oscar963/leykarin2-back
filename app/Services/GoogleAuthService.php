<?php

namespace App\Services;

use App\Models\User;
use App\Traits\LogsActivity;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Carbon\Carbon;

class GoogleAuthService
{
    use LogsActivity;

    protected GoogleClient $googleClient;
    protected SecurityLogService $securityLogService;

    public function __construct(SecurityLogService $securityLogService)
    {
        $this->securityLogService = $securityLogService;
        $this->initializeGoogleClient();
    }

    /**
     * Inicializa el cliente de Google.
     */
    protected function initializeGoogleClient(): void
    {
        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(config('services.google.client_secret'));

        // Configuración SSL para desarrollo local
        if (app()->environment('local')) {
            $guzzleConfig = [
                'timeout' => 30,
                'connect_timeout' => 10,
            ];

            // Si hay un path de certificados CA configurado, usarlo
            $caBundlePath = env('CURL_CA_BUNDLE_PATH');
            if ($caBundlePath && file_exists($caBundlePath)) {
                $guzzleConfig['verify'] = $caBundlePath;
            } else {
                // Solo para desarrollo: deshabilitar verificación SSL
                $guzzleConfig['verify'] = false;
                Log::warning('SSL verification disabled for Google OAuth in local environment');
            }

            $httpClient = new \GuzzleHttp\Client($guzzleConfig);
            $this->googleClient->setHttpClient($httpClient);
        }
    }

    /**
     * Verifica y valida el ID Token de Google.
     *
     * @param string $idToken
     * @return array
     * @throws AuthorizationException
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            // Verificar el token usando la librería oficial de Google
            $payload = $this->googleClient->verifyIdToken($idToken);

            if (!$payload) {
                throw new AuthorizationException('ID Token de Google inválido o no verificado.');
            }

            // Verificar la audiencia (aud)
            if ($payload['aud'] !== config('services.google.client_id')) {
                throw new AuthorizationException('ID Token no destinado a esta aplicación (Audiencia no válida).');
            }

            // Verificar el emisor (iss)
            $validIssuers = ['accounts.google.com', 'https://accounts.google.com'];
            if (!in_array($payload['iss'], $validIssuers)) {
                throw new AuthorizationException('Emisor del token no válido.');
            }

            // Verificar expiración
            if ($payload['exp'] < time()) {
                throw new AuthorizationException('ID Token ha expirado.');
            }

            Log::info('Google ID Token verificado exitosamente', [
                'google_id' => $payload['sub'],
                'email' => $payload['email'],
                'domain' => $payload['hd'] ?? null
            ]);

            return $payload;
        } catch (\Exception $e) {
            Log::error('Error verificando Google ID Token', [
                'error' => $e->getMessage(),
                'token_preview' => substr($idToken, 0, 50) . '...'
            ]);
            throw new AuthorizationException('Error de autenticación: ' . $e->getMessage());
        }
    }

    /**
     * Valida el dominio corporativo si está configurado.
     *
     * @param array $payload
     * @throws AuthorizationException
     */
    public function validateCorporateDomain(array $payload): void
    {
        $allowedDomain = config('services.google.allowed_domain');

        // Si no hay dominio configurado o es '*', permitir todos los dominios
        if (!$allowedDomain || $allowedDomain === '*') {
            Log::info('Acceso permitido desde cualquier dominio', [
                'email' => $payload['email'],
                'domain' => $payload['hd'] ?? 'gmail.com (personal)'
            ]);
            return;
        }

        // Si hay dominio específico configurado, validarlo
        $userDomain = $payload['hd'] ?? null;

        if ($userDomain !== $allowedDomain) {
            Log::warning('Intento de acceso con dominio no autorizado', [
                'user_domain' => $userDomain,
                'allowed_domain' => $allowedDomain,
                'email' => $payload['email']
            ]);

            throw new AuthorizationException(
                "Dominio de correo no autorizado. Solo se permite el acceso a {$allowedDomain}"
            );
        }
    }

    /**
     * Busca o crea un usuario basado en los datos de Google.
     *
     * @param array $payload
     * @return User
     * @throws AuthorizationException
     */
    public function findOrCreateUser(array $payload): User
    {
        $googleId = $payload['sub'];
        $email = $payload['email'];

        // Primero buscar por Google ID
        $user = User::findByGoogleId($googleId);

        if ($user) {
            // Usuario existente con Google ID, actualizar información
            $user->updateGoogleInfo($payload);

            Log::info('Usuario existente autenticado con Google', [
                'user_id' => $user->id,
                'email' => $email
            ]);

            return $user;
        }

        // Buscar por email
        $user = User::findByGoogleEmail($email);

        if ($user) {
            // Usuario existe pero sin Google ID, vincular cuenta
            $user->updateGoogleInfo($payload);

            Log::info('Cuenta existente vinculada con Google', [
                'user_id' => $user->id,
                'email' => $email
            ]);

            return $user;
        }

        // Usuario no existe
        if (!config('services.google.auto_register', false)) {
            Log::warning('Intento de acceso de usuario no registrado', [
                'email' => $email,
                'google_id' => $googleId
            ]);

            throw new AuthorizationException(
                'Usuario no registrado en el sistema.'
            );
        }

        // Auto-registro habilitado (opcional)
        return $this->createUserFromGoogle($payload);
    }

    /**
     * Crea un nuevo usuario desde los datos de Google (solo si auto-registro está habilitado).
     *
     * @param array $payload
     * @return User
     */
    protected function createUserFromGoogle(array $payload): User
    {
        $userData = [
            'name' => $payload['given_name'] ?? $payload['name'],
            'paternal_surname' => $payload['family_name'] ?? '',
            'maternal_surname' => '',
            'email' => $payload['email'],
            'google_id' => $payload['sub'],
            'google_email' => $payload['email'],
            'google_name' => $payload['name'],
            'google_avatar' => $payload['picture'] ?? null,
            'google_verified_at' => Carbon::now(),
            'google_domain' => $payload['hd'] ?? null,
            'auth_provider' => 'google',
            'status' => true,
            'email_verified_at' => Carbon::now(), // Google ya verificó el email
        ];

        $user = User::create($userData);

        // Asignar rol por defecto si está configurado
        $defaultRole = config('services.google.default_role');
        if ($defaultRole && $user->hasRole($defaultRole) === false) {
            $user->assignRole($defaultRole);
        }

        Log::info('Nuevo usuario creado desde Google OAuth', [
            'user_id' => $user->id,
            'email' => $user->email,
            'google_id' => $user->google_id
        ]);

        return $user;
    }

    /**
     * Autentica al usuario usando cookies de sesión (stateful).
     *
     * @param User $user
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function authenticateUser(User $user, $request): array
    {
        // Verificar que la cuenta esté activa
        if (!$user->status) {
            $this->securityLogService->logSuspendedAccountLogin($user, $request);
            throw new AuthorizationException(
                'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'
            );
        }

        // Verificar dominio de Google si es necesario
        if (!$user->isGoogleDomainAllowed()) {
            throw new AuthorizationException('Dominio de Google no autorizado para este usuario.');
        }

        // Autenticar usando sesión (cookies) en lugar de token
        \Illuminate\Support\Facades\Auth::login($user, true); // true = remember me

        // Regenerar sesión para seguridad
        $request->session()->regenerate();

        // Logging de seguridad
        $this->securityLogService->logSuccessfulLogin($user, $request);
        $this->logActivity('google_login', 'Usuario inició sesión con Google OAuth exitosamente.');

        Log::info('Usuario autenticado exitosamente con Google OAuth', [
            'user_id' => $user->id,
            'email' => $user->email,
            'auth_method' => 'session'
        ]);

        return [
            'user' => $user->load(['roles', 'permissions', 'typeDependency']),
            'auth_method' => 'session',
        ];
    }

    /**
     * Verifica si Google OAuth está habilitado.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('services.google.enabled', true) &&
            !empty(config('services.google.client_id'));
    }

    /**
     * Obtiene la configuración de Google OAuth para el frontend.
     *
     * @return array
     */
    public function getClientConfig(): array
    {
        return [
            'client_id' => config('services.google.client_id'),
            'enabled' => $this->isEnabled(),
            'allowed_domain' => config('services.google.allowed_domain'),
            'auto_register' => config('services.google.auto_register', false),
        ];
    }
}
