# Configuración Laravel Fortify con RUT Chileno

Esta documentación describe la configuración completa de Laravel Fortify para autenticación usando RUT chileno como campo de usuario, integrado con Laravel Sanctum para SPA (Single Page Application) con Angular.

## 📋 Requisitos Previos

- Laravel 9+
- Laravel Fortify
- Laravel Sanctum
- Angular frontend en `localhost:4200`
- Backend en `localhost:8000`

## 🔧 Configuración Paso a Paso

### 1. Instalación de Paquetes

```bash
composer require laravel/fortify
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
php artisan migrate
```

### 2. Variables de Entorno (.env)

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:4200
SESSION_DOMAIN=localhost
SESSION_DRIVER=database
```

### 3. Configuración de Fortify (config/fortify.php)

```php
<?php
return [
    // Usar 'rut' como campo de usuario en lugar de 'email'
    'username' => 'rut',
    
    // Prefijo para las rutas de Fortify
    'prefix' => 'api/v1/auth',
    
    // Middleware para las rutas (usar 'web' para SPA con Sanctum)
    'middleware' => ['web'],
    
    // Desactivar vistas (API only)
    'views' => false,
    
    // No convertir usernames a minúsculas (importante para RUTs)
    'lowercase_usernames' => false,
    
    // Features habilitadas
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
    
    // Limitadores de velocidad
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],
];
```

### 4. Registro de Providers (config/app.php)

Asegurar que ambos providers estén registrados:

```php
'providers' => [
    // ... otros providers
    Laravel\Fortify\FortifyServiceProvider::class,
    App\Providers\FortifyServiceProvider::class, // ← CRÍTICO: Tu provider personalizado
    // ... otros providers
],
```

### 5. FortifyServiceProvider Personalizado (app/Providers/FortifyServiceProvider.php)

```php
<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // CRÍTICO: Configurar 'rut' como campo de usuario
        Fortify::username('rut');

        // Rate Limiters
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Autenticación personalizada con RUT
        Fortify::authenticateUsing(function (Request $request) {
            // Normalizar RUT usando helper personalizado
            $rut = \App\Helpers\RutHelper::normalize($request->rut);
            if (!$rut) {
                return null;
            }

            // Buscar usuario por RUT normalizado
            $user = User::where('rut', $rut)->first();

            // Verificar contraseña y estado activo
            if ($user && 
                Hash::check($request->password, $user->password) && 
                $user->status) {
                return $user;
            }

            return null;
        });
    }
}
```

### 6. Configuración CORS (config/cors.php)

```php
'allowed_origins' => ['http://localhost:4200'],
'supports_credentials' => true,
```

### 7. Middleware CSRF (app/Http/Middleware/VerifyCsrfToken.php)

```php
protected $except = [
    'telescope/*', // Solo excluir telescope, NO excluir 'api/*'
];
```

### 8. Rutas API (routes/api.php)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Solo mantener la ruta de usuario autenticado
// Las rutas de login/logout las maneja Fortify automáticamente
Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');
});

// Otras rutas protegidas...
```

### 9. Rutas Web para OAuth (routes/web.php)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Rutas de Clave Única (OAuth) deben usar middleware 'web'
Route::prefix('auth')->group(function () {
    Route::get('/claveunica/redirect', [AuthController::class, 'redirectToClaveUnica'])
        ->name('auth.claveunica.redirect');
    Route::get('/claveunica/callback', [AuthController::class, 'handleClaveUnicaCallback'])
        ->name('auth.claveunica.callback');
});
```

## 🚀 Rutas Generadas por Fortify

Con la configuración anterior, Fortify genera automáticamente estas rutas:

- `POST /api/v1/auth/login` - Iniciar sesión
- `POST /api/v1/auth/logout` - Cerrar sesión
- `POST /api/v1/auth/forgot-password` - Solicitar reset de contraseña
- `POST /api/v1/auth/reset-password` - Resetear contraseña
- `POST /api/v1/auth/user/confirm-password` - Confirmar contraseña
- Rutas de 2FA y perfil de usuario

## 🔍 Frontend Angular - Configuración

### Flujo de Autenticación

```typescript
// 1. Obtener cookie CSRF antes del login
this.http.get('http://localhost:8000/sanctum/csrf-cookie', { 
  withCredentials: true 
}).subscribe(() => {
  
  // 2. Hacer login
  this.http.post('http://localhost:8000/api/v1/auth/login', {
    rut: '12345678-5',
    password: 'password123',
    remember: false
  }, { 
    withCredentials: true 
  }).subscribe(response => {
    
    // 3. Obtener datos del usuario autenticado
    this.http.get('http://localhost:8000/api/v1/auth/user', {
      withCredentials: true
    }).subscribe(user => {
      console.log('Usuario autenticado:', user);
    });
  });
});
```

### Configuración HTTP Interceptor

```typescript
// Asegurar que todas las requests incluyan withCredentials
@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const authReq = req.clone({
      setHeaders: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      withCredentials: true
    });
    return next.handle(authReq);
  }
}
```

## 🛠️ Comandos de Mantenimiento

```bash
# Limpiar cachés después de cambios de configuración
php artisan optimize:clear

# Servir la aplicación en localhost (importante para cookies)
php artisan serve --host=localhost --port=8000

# Verificar rutas generadas
php artisan route:list --path=api/v1/auth
```

## ⚠️ Problemas Comunes y Soluciones

### 1. Error 422 "Estas credenciales no coinciden"

**Causa**: Provider personalizado no registrado en `config/app.php`
**Solución**: Agregar `App\Providers\FortifyServiceProvider::class` al array de providers

### 2. Callback `authenticateUsing()` no se ejecuta

**Causa**: Features de Fortify deshabilitadas
**Solución**: Habilitar features básicas en `config/fortify.php`

### 3. Problemas con cookies CSRF

**Causa**: Dominio mismatch entre frontend/backend
**Solución**: Usar `localhost` en ambos lados, no `127.0.0.1`

### 4. RUT no se normaliza correctamente

**Causa**: `lowercase_usernames => true`
**Solución**: Cambiar a `false` en `config/fortify.php`

## 📝 Notas Importantes

1. **Orden de providers**: `Laravel\Fortify\FortifyServiceProvider` debe ir ANTES que `App\Providers\FortifyServiceProvider`
2. **Middleware**: Usar `web` para SPA con Sanctum, no `api`
3. **CSRF**: Siempre llamar `/sanctum/csrf-cookie` antes del primer POST
4. **Cookies**: Usar `withCredentials: true` en todas las requests del frontend
5. **Dominios**: Mantener consistencia entre `localhost:4200` y `localhost:8000`

## 🔐 Seguridad

- Rate limiting configurado (5 intentos por minuto)
- CSRF protection habilitado
- Verificación de estado de usuario (`status` field)
- Normalización y validación de RUT
- Logging de intentos de autenticación para auditoría

---

**Autor**: Sistema de Autenticación Bienes Inmuebles  
**Fecha**: Agosto 2025  
**Versión**: 1.0
