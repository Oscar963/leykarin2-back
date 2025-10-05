# 🔐 Guía de Implementación - Mejoras de Seguridad

**Sistema:** Leykarin2 - Sistema de Denuncias  
**Fecha:** 04 de Octubre, 2025  
**Versión:** 1.0

---

## 📋 Índice

1. [Prioridad 1 - CRÍTICO](#prioridad-1---crítico)
2. [Prioridad 2 - ALTO](#prioridad-2---alto)
3. [Prioridad 3 - MEDIO](#prioridad-3---medio)
4. [Configuración de Angular](#configuración-de-angular)
5. [Testing y Validación](#testing-y-validación)
6. [Rollback y Troubleshooting](#rollback-y-troubleshooting)

---

## 🔴 Prioridad 1 - CRÍTICO

### 1.1 Configurar Cookies Seguras en Producción

**Tiempo estimado:** 5 minutos  
**Impacto:** CRÍTICO

#### Paso 1: Actualizar `.env` de producción

```env
# .env (PRODUCCIÓN)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_DOMAIN=.imaarica.cl
SESSION_ENCRYPT=true
```

#### Paso 2: Verificar configuración

```bash
# En el servidor de producción
php artisan config:clear
php artisan config:cache
php artisan config:show session
```

#### Paso 3: Validar

```bash
# Verificar que las cookies tengan los flags correctos
curl -I https://leykarin2.imaarica.cl/sanctum/csrf-cookie

# Debe mostrar:
# Set-Cookie: laravel_session=...; Secure; SameSite=None
```

**⚠️ IMPORTANTE:**
- `SESSION_SAME_SITE=none` REQUIERE `SESSION_SECURE_COOKIE=true`
- Solo usar `SameSite=none` si frontend y backend están en dominios diferentes
- Si están en el mismo dominio, usar `SESSION_SAME_SITE=lax`

---

### 1.2 Implementar Regeneración de Token CSRF

**Tiempo estimado:** 10 minutos  
**Impacto:** ALTO

#### Paso 1: Modificar `AuthController.php`

```php
// app/Http/Controllers/Auth/AuthController.php

protected function sendSuccessfulAuthenticationResponse(Request $request, User $user): JsonResponse
{
    Auth::login($user, $request->session()->pull('login.remember', false));
    
    // Regenerar session ID
    $request->session()->regenerate();
    
    // ✅ NUEVO: Regenerar token CSRF
    $request->session()->regenerateToken();

    return response()->json([
        'message' => "Bienvenido(a) al sistema {$user->name} {$user->paternal_surname}",
        'user' => new AuthResource($user->loadMissing(['roles', 'permissions']))
    ]);
}
```

#### Paso 2: Aplicar también en logout

```php
// app/Http/Controllers/Auth/AuthController.php

public function logout(Request $request): JsonResponse
{
    $this->securityLogService->logLogout($request->user(), $request);
    $this->logActivity('logout', 'Usuario cerró sesión.');

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();   // Ya implementado ✅

    return response()->json(['message' => 'Cerró sesión exitosamente.']);
}
```

---

### 1.3 Verificar Configuración de Angular

**Tiempo estimado:** 15 minutos  
**Impacto:** CRÍTICO

Ver sección completa: [Configuración de Angular](#configuración-de-angular)

---

## 🟡 Prioridad 2 - ALTO

### 2.1 Migrar Sesiones a Redis

**Tiempo estimado:** 30 minutos  
**Impacto:** ALTO

#### Paso 1: Instalar Redis (si no está instalado)

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server
sudo systemctl start redis
sudo systemctl enable redis

# Verificar instalación
redis-cli ping
# Debe responder: PONG
```

#### Paso 2: Instalar extensión PHP Redis

```bash
# Ubuntu/Debian
sudo apt install php-redis

# Verificar instalación
php -m | grep redis
```

#### Paso 3: Configurar Laravel

```env
# .env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null   # Configurar contraseña en producción
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

#### Paso 4: Limpiar caché y probar

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Probar conexión
php artisan tinker
>>> Redis::connection()->ping()
# Debe responder: "PONG"
```

#### Paso 5: Configurar contraseña de Redis (PRODUCCIÓN)

```bash
# Editar configuración de Redis
sudo nano /etc/redis/redis.conf

# Buscar y descomentar:
requirepass tu_contraseña_segura_aqui

# Reiniciar Redis
sudo systemctl restart redis

# Actualizar .env
REDIS_PASSWORD=tu_contraseña_segura_aqui
```

---

### 2.2 Habilitar Encriptación de Sesiones

**Tiempo estimado:** 5 minutos  
**Impacto:** MEDIO

#### Paso 1: Actualizar configuración

```php
// config/session.php
'encrypt' => true,   // Cambiar de false a true
```

#### Paso 2: Limpiar caché

```bash
php artisan config:clear
php artisan config:cache
```

**Nota:** Esto encriptará el contenido de las sesiones en Redis/DB, no la cookie (que ya está firmada).

---

### 2.3 Implementar Timeout de Inactividad

**Tiempo estimado:** 10 minutos  
**Impacto:** MEDIO

#### Paso 1: Registrar middleware en `Kernel.php`

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ... middlewares existentes
    'inactivity.timeout' => \App\Http\Middleware\CheckInactivityTimeout::class,
];
```

#### Paso 2: Aplicar a rutas protegidas

```php
// routes/api.php

Route::middleware(['auth:sanctum', 'active.user', 'inactivity.timeout'])->group(function () {
    // Todas las rutas protegidas
});
```

#### Paso 3: Configurar timeout

```php
// config/session.php

return [
    // ... configuración existente
    
    // Timeout de inactividad en segundos (30 minutos)
    'inactivity_timeout' => env('SESSION_INACTIVITY_TIMEOUT', 1800),
];
```

```env
# .env
SESSION_INACTIVITY_TIMEOUT=1800   # 30 minutos
```

---

### 2.4 Habilitar AuthenticateSession Middleware

**Tiempo estimado:** 5 minutos  
**Impacto:** MEDIO

#### Paso 1: Descomentar en `Kernel.php`

```php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,   // ✅ Descomentar
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
    // ...
];
```

#### Paso 2: Limpiar caché

```bash
php artisan config:clear
php artisan route:clear
```

**Funcionalidad:** Invalida automáticamente sesiones si la contraseña del usuario cambia.

---

## 🟢 Prioridad 3 - MEDIO

### 3.1 Implementar Validación de User-Agent

**Tiempo estimado:** 10 minutos  
**Impacto:** MEDIO

#### Paso 1: Registrar middleware

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ... middlewares existentes
    'validate.user.agent' => \App\Http\Middleware\ValidateUserAgent::class,
];
```

#### Paso 2: Aplicar a rutas sensibles

```php
// routes/api.php

Route::middleware(['auth:sanctum', 'active.user', 'validate.user.agent'])->group(function () {
    // Rutas protegidas
});
```

**Nota:** Este middleware cerrará la sesión si detecta cambio de User-Agent.

---

### 3.2 Implementar Detección de Sesiones Concurrentes

**Tiempo estimado:** 15 minutos  
**Impacto:** MEDIO

#### Paso 1: Registrar middleware

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ... middlewares existentes
    'concurrent.sessions' => \App\Http\Middleware\DetectConcurrentSessions::class,
];
```

#### Paso 2: Configurar

```php
// config/session.php

return [
    // ... configuración existente
    
    // Número máximo de sesiones concurrentes (0 = ilimitado)
    'max_concurrent_sessions' => env('SESSION_MAX_CONCURRENT', 3),
    
    // Estrategia: notify|block|logout_oldest
    'concurrent_strategy' => env('SESSION_CONCURRENT_STRATEGY', 'notify'),
];
```

```env
# .env
SESSION_MAX_CONCURRENT=3
SESSION_CONCURRENT_STRATEGY=notify
```

#### Paso 3: Aplicar middleware

```php
// routes/api.php

Route::middleware(['auth:sanctum', 'active.user', 'concurrent.sessions'])->group(function () {
    // Rutas protegidas
});
```

#### Estrategias disponibles:

- **notify**: Solo notifica, permite acceso (recomendado)
- **block**: Bloquea nueva sesión si se excede el límite
- **logout_oldest**: Cierra automáticamente la sesión más antigua

---

### 3.3 Configurar IP Whitelisting (Opcional)

**Tiempo estimado:** 20 minutos  
**Impacto:** BAJO

#### Paso 1: Crear middleware

```php
// app/Http/Middleware/IpWhitelist.php

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    protected $whitelist = [];

    public function __construct()
    {
        $this->whitelist = explode(',', env('IP_WHITELIST', ''));
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (empty($this->whitelist)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (!in_array($clientIp, $this->whitelist)) {
            return response()->json([
                'message' => 'Acceso denegado desde esta IP.',
                'error' => 'IP_NOT_WHITELISTED'
            ], 403);
        }

        return $next($request);
    }
}
```

#### Paso 2: Registrar y aplicar

```php
// app/Http/Kernel.php
'ip.whitelist' => \App\Http\Middleware\IpWhitelist::class,

// routes/api.php
Route::middleware(['auth:sanctum', 'ip.whitelist'])->prefix('admin')->group(function () {
    // Rutas administrativas
});
```

```env
# .env
IP_WHITELIST=192.168.1.100,192.168.1.101,10.0.0.50
```

---

## 🎯 Configuración de Angular

### Configuración Correcta para Angular 19

#### 1. Environment Configuration

```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api/v1',
  sanctumUrl: 'http://localhost:8000',
  withCredentials: true
};

// src/environments/environment.prod.ts
export const environment = {
  production: true,
  apiUrl: 'https://leykarin2.imaarica.cl/api/v1',
  sanctumUrl: 'https://leykarin2.imaarica.cl',
  withCredentials: true
};
```

#### 2. HTTP Interceptor

```typescript
// src/app/core/interceptors/http.interceptor.ts
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable()
export class HttpConfigInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // Clonar request y agregar withCredentials
    const clonedReq = req.clone({
      withCredentials: true,
      headers: req.headers
        .set('Accept', 'application/json')
        .set('X-Requested-With', 'XMLHttpRequest')
    });

    return next.handle(clonedReq);
  }
}
```

#### 3. Auth Service

```typescript
// src/app/core/services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { switchMap, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private sanctumUrl = environment.sanctumUrl;

  constructor(private http: HttpClient) {}

  /**
   * PASO 1: Obtener CSRF token
   * PASO 2: Hacer login
   */
  login(credentials: { rut: string; password: string }): Observable<any> {
    // Primero obtener CSRF token
    return this.getCsrfToken().pipe(
      // Luego hacer login
      switchMap(() => 
        this.http.post(`${this.apiUrl}/auth/login`, credentials, {
          withCredentials: true
        })
      )
    );
  }

  /**
   * Obtener CSRF token de Sanctum
   */
  getCsrfToken(): Observable<any> {
    return this.http.get(`${this.sanctumUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    });
  }

  /**
   * Obtener usuario autenticado
   */
  getUser(): Observable<any> {
    return this.http.get(`${this.apiUrl}/auth/user`, {
      withCredentials: true
    });
  }

  /**
   * Logout
   */
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/auth/logout`, {}, {
      withCredentials: true
    });
  }

  /**
   * Verificar si hay sesión activa
   */
  checkSession(): Observable<any> {
    return this.http.get(`${this.apiUrl}/auth/user`, {
      withCredentials: true
    });
  }
}
```

#### 4. App Module Configuration

```typescript
// src/app/app.module.ts
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { HttpConfigInterceptor } from './core/interceptors/http.interceptor';

@NgModule({
  // ...
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpConfigInterceptor,
      multi: true
    }
  ]
})
export class AppModule { }
```

#### 5. Proxy Configuration (Desarrollo)

```json
// proxy.conf.json
{
  "/api": {
    "target": "http://localhost:8000",
    "secure": false,
    "changeOrigin": true,
    "logLevel": "debug"
  },
  "/sanctum": {
    "target": "http://localhost:8000",
    "secure": false,
    "changeOrigin": true,
    "logLevel": "debug"
  }
}
```

```json
// angular.json
{
  "projects": {
    "your-app": {
      "architect": {
        "serve": {
          "options": {
            "proxyConfig": "proxy.conf.json"
          }
        }
      }
    }
  }
}
```

#### 6. Auth Guard

```typescript
// src/app/core/guards/auth.guard.ts
import { Injectable } from '@angular/core';
import { Router, CanActivate } from '@angular/router';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(): Observable<boolean> {
    return this.authService.checkSession().pipe(
      map(user => {
        if (user) {
          return true;
        }
        this.router.navigate(['/login']);
        return false;
      }),
      catchError(() => {
        this.router.navigate(['/login']);
        return of(false);
      })
    );
  }
}
```

---

## 🧪 Testing y Validación

### Test 1: Verificar Cookies Seguras

```bash
# Producción
curl -I https://leykarin2.imaarica.cl/sanctum/csrf-cookie

# Verificar headers:
# Set-Cookie: laravel_session=...; Secure; HttpOnly; SameSite=None
# Set-Cookie: XSRF-TOKEN=...; Secure; SameSite=None
```

### Test 2: Verificar CSRF Protection

```bash
# Debe fallar sin CSRF token
curl -X POST https://leykarin2.imaarica.cl/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"rut":"12345678-9","password":"test"}'

# Debe responder: 419 CSRF token mismatch
```

### Test 3: Verificar Rate Limiting

```bash
# Hacer 10 intentos de login fallidos
for i in {1..10}; do
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"rut":"12345678-9","password":"wrong"}'
  echo ""
done

# Después del 5to intento debe responder: 429 Too Many Requests
```

### Test 4: Verificar Timeout de Inactividad

```typescript
// En Angular - Simular inactividad
setTimeout(() => {
  this.authService.getUser().subscribe(
    user => console.log('Sesión activa'),
    error => console.log('Sesión expirada')  // Debe fallar después de 30 min
  );
}, 31 * 60 * 1000);  // 31 minutos
```

### Test 5: Verificar Headers de Seguridad

```bash
curl -I https://leykarin2.imaarica.cl/api/v1/auth/user

# Verificar headers:
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# X-Content-Type-Options: nosniff
# Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
# Content-Security-Policy: ...
```

---

## 🔄 Rollback y Troubleshooting

### Rollback de Sesiones a File

```env
# .env
SESSION_DRIVER=file
CACHE_DRIVER=file
```

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### Problema: Cookies no se envían desde Angular

**Causa:** `withCredentials: true` no configurado

**Solución:**
```typescript
// Verificar que TODAS las peticiones tengan:
this.http.get(url, { withCredentials: true })
```

### Problema: Error 419 CSRF Token Mismatch

**Causa:** No se llamó a `/sanctum/csrf-cookie` antes del login

**Solución:**
```typescript
// Siempre llamar primero:
this.getCsrfToken().pipe(
  switchMap(() => this.login(credentials))
)
```

### Problema: Sesión se pierde en cada request

**Causa:** `SESSION_SAME_SITE` o `SESSION_DOMAIN` mal configurado

**Solución:**
```env
# Si frontend y backend en dominios diferentes:
SESSION_SAME_SITE=none
SESSION_SECURE_COOKIE=true

# Si en el mismo dominio:
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.imaarica.cl
```

### Problema: Redis connection refused

**Solución:**
```bash
# Verificar que Redis esté corriendo
sudo systemctl status redis

# Si no está corriendo:
sudo systemctl start redis

# Verificar conexión:
redis-cli ping
```

### Logs útiles para debugging

```bash
# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs de seguridad
tail -f storage/logs/security.log

# Logs de Redis
sudo tail -f /var/log/redis/redis-server.log

# Logs de Nginx
sudo tail -f /var/log/nginx/error.log
```

---

## 📊 Checklist de Implementación

### Prioridad 1 - CRÍTICO
- [ ] Configurar `SESSION_SECURE_COOKIE=true` en producción
- [ ] Configurar `SESSION_SAME_SITE=none` (si cross-origin)
- [ ] Implementar regeneración de token CSRF en login
- [ ] Verificar `withCredentials: true` en Angular

### Prioridad 2 - ALTO
- [ ] Migrar sesiones a Redis
- [ ] Habilitar encriptación de sesiones
- [ ] Implementar timeout de inactividad
- [ ] Habilitar `AuthenticateSession` middleware

### Prioridad 3 - MEDIO
- [ ] Implementar validación de User-Agent
- [ ] Implementar detección de sesiones concurrentes
- [ ] Configurar IP whitelisting (opcional)
- [ ] Mejorar CSP con nonces

### Testing
- [ ] Test de cookies seguras
- [ ] Test de CSRF protection
- [ ] Test de rate limiting
- [ ] Test de timeout de inactividad
- [ ] Test de headers de seguridad

---

**Generado por:** Cascade AI  
**Fecha:** 04 de Octubre, 2025  
**Versión:** 1.0
