# Configuración de CSRF para Angular con Laravel Sanctum

## 🎯 Objetivo

Implementar el flujo correcto de CSRF entre Angular y Laravel para que las rutas con middleware `web` funcionen correctamente en producción.

## 🔍 El Problema

Las rutas de autenticación tienen middleware `web` que incluye verificación CSRF:
```php
Route::prefix('auth')->middleware(['web'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // ...
});
```

Angular necesita:
1. **Obtener el token CSRF** de Laravel
2. **Enviarlo en cada request** que requiera CSRF

## ✅ Solución: Implementar Sanctum CSRF Cookie

### **Paso 1: Configurar Interceptor HTTP en Angular**

Crea o actualiza tu interceptor HTTP para manejar CSRF:

```typescript
// src/app/interceptors/csrf.interceptor.ts
import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpXsrfTokenExtractor
} from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class CsrfInterceptor implements HttpInterceptor {
  
  constructor(private tokenExtractor: HttpXsrfTokenExtractor) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    // Solo agregar CSRF token para requests que lo necesiten
    const token = this.tokenExtractor.getToken();
    
    if (token !== null && !request.headers.has('X-XSRF-TOKEN')) {
      request = request.clone({
        headers: request.headers.set('X-XSRF-TOKEN', token),
        withCredentials: true // Importante: enviar cookies
      });
    }

    return next.handle(request);
  }
}
```

### **Paso 2: Configurar HttpClient en app.module.ts**

```typescript
// src/app/app.module.ts
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule, HttpClientXsrfModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { CsrfInterceptor } from './interceptors/csrf.interceptor';

@NgModule({
  declarations: [
    // ... tus componentes
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    // Configurar XSRF/CSRF
    HttpClientXsrfModule.withOptions({
      cookieName: 'XSRF-TOKEN',
      headerName: 'X-XSRF-TOKEN'
    }),
    // ... otros módulos
  ],
  providers: [
    // Registrar interceptor
    {
      provide: HTTP_INTERCEPTORS,
      useClass: CsrfInterceptor,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
```

### **Paso 3: Crear Servicio de Autenticación**

```typescript
// src/app/services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /**
   * Obtiene el token CSRF de Laravel antes de hacer login
   */
  getCsrfCookie(): Observable<void> {
    return this.http.get<void>(`${this.apiUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    });
  }

  /**
   * Login con CSRF token
   */
  login(rut: string, password: string, remember: boolean = false): Observable<any> {
    // Primero obtener el CSRF cookie
    return this.getCsrfCookie().pipe(
      tap(() => {
        // Después hacer el login
        return this.http.post(`${this.apiUrl}/api/v1/auth/login`, {
          rut,
          password,
          remember
        }, {
          withCredentials: true
        });
      })
    );
  }

  /**
   * Alternativa: Login con encadenamiento explícito
   */
  loginWithCsrf(rut: string, password: string, remember: boolean = false): Observable<any> {
    return new Observable(observer => {
      // Paso 1: Obtener CSRF cookie
      this.getCsrfCookie().subscribe({
        next: () => {
          // Paso 2: Hacer login
          this.http.post(`${this.apiUrl}/api/v1/auth/login`, {
            rut,
            password,
            remember
          }, {
            withCredentials: true
          }).subscribe({
            next: (response) => {
              observer.next(response);
              observer.complete();
            },
            error: (error) => observer.error(error)
          });
        },
        error: (error) => observer.error(error)
      });
    });
  }

  /**
   * Logout
   */
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/v1/auth/logout`, {}, {
      withCredentials: true
    });
  }
}
```

### **Paso 4: Usar en Componente de Login**

```typescript
// src/app/components/login/login.component.ts
import { Component } from '@angular/core';
import { AuthService } from 'src/app/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html'
})
export class LoginComponent {
  
  rut: string = '';
  password: string = '';
  remember: boolean = false;
  loading: boolean = false;
  error: string = '';

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  onSubmit(): void {
    this.loading = true;
    this.error = '';

    this.authService.loginWithCsrf(this.rut, this.password, this.remember)
      .subscribe({
        next: (response) => {
          console.log('Login exitoso:', response);
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          console.error('Error en login:', error);
          this.error = error.error?.message || 'Error al iniciar sesión';
          this.loading = false;
        },
        complete: () => {
          this.loading = false;
        }
      });
  }
}
```

## 🔧 Configuración de Laravel (Backend)

### **1. Configurar CORS correctamente**

En `config/cors.php`:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200')),
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.imaarica\.cl$/'
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // ← IMPORTANTE
];
```

### **2. Configurar Sanctum Stateful Domains**

En `.env`:
```env
# Desarrollo
SANCTUM_STATEFUL_DOMAINS=localhost:4200,127.0.0.1:4200

# Producción
SANCTUM_STATEFUL_DOMAINS=leykarin2.imaarica.cl
```

### **3. Configurar Session para producción**

En `.env` de producción:
```env
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_DOMAIN=.imaarica.cl
```

## 📋 Variables de Entorno

### **Angular (environment.ts)**

```typescript
// src/environments/environment.ts (desarrollo)
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'
};

// src/environments/environment.prod.ts (producción)
export const environment = {
  production: true,
  apiUrl: 'https://leykarin2.imaarica.cl'
};
```

### **Laravel (.env)**

**Desarrollo:**
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200
CORS_ALLOWED_ORIGINS=http://localhost:4200
SANCTUM_STATEFUL_DOMAINS=localhost:4200,127.0.0.1:4200
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
SESSION_DOMAIN=localhost
```

**Producción:**
```env
APP_URL=https://leykarin2.imaarica.cl
FRONTEND_URL=https://leykarin2.imaarica.cl
CORS_ALLOWED_ORIGINS=https://leykarin2.imaarica.cl
SANCTUM_STATEFUL_DOMAINS=leykarin2.imaarica.cl
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_DOMAIN=.imaarica.cl
```

## 🧪 Probar la Configuración

### **1. Verificar que CSRF cookie se obtiene**

En DevTools → Network:
1. Buscar request a `/sanctum/csrf-cookie`
2. Verificar que retorna cookie `XSRF-TOKEN`

### **2. Verificar que login envía CSRF token**

En DevTools → Network → Request a `/api/v1/auth/login`:
1. Headers → Request Headers
2. Debe incluir: `X-XSRF-TOKEN: <token>`
3. Debe incluir: `Cookie: XSRF-TOKEN=<token>`

### **3. Verificar CORS**

En Console, no debe haber errores CORS como:
```
Access to XMLHttpRequest at '...' from origin '...' has been blocked by CORS policy
```

## ⚠️ Problemas Comunes

### **Error: "CSRF token mismatch"**

**Causas:**
1. No se está obteniendo el CSRF cookie antes del login
2. `withCredentials: true` no está configurado
3. Dominio no está en `SANCTUM_STATEFUL_DOMAINS`
4. CORS no permite credentials

**Solución:**
- Verificar que se llama a `/sanctum/csrf-cookie` primero
- Asegurar `withCredentials: true` en todas las requests
- Verificar configuración de CORS y Sanctum

### **Error: CORS**

**Causas:**
1. `CORS_ALLOWED_ORIGINS` no incluye el dominio del frontend
2. `supports_credentials` no está en `true`

**Solución:**
- Actualizar `.env` con el dominio correcto
- Limpiar cache: `php artisan config:clear`

### **Cookies no se envían**

**Causas:**
1. `withCredentials: true` falta en Angular
2. `SESSION_SAME_SITE` incorrecto para HTTPS
3. Dominio de cookie no coincide

**Solución:**
- En producción usar `SESSION_SAME_SITE=none` con HTTPS
- Configurar `SESSION_DOMAIN=.imaarica.cl`

## 📊 Flujo Completo

```
1. Usuario abre página de login
   ↓
2. Angular llama GET /sanctum/csrf-cookie
   ↓
3. Laravel retorna cookie XSRF-TOKEN
   ↓
4. Usuario ingresa credenciales y hace submit
   ↓
5. Angular envía POST /api/v1/auth/login
   - Header: X-XSRF-TOKEN: <token>
   - Cookie: XSRF-TOKEN=<token>
   ↓
6. Laravel verifica CSRF token
   ↓
7. Laravel autentica usuario
   ↓
8. Laravel retorna respuesta exitosa
   ↓
9. Angular guarda sesión y redirige
```

## 🔒 Seguridad

Esta configuración mantiene:
- ✅ **Protección CSRF** en rutas de autenticación
- ✅ **Cookies HttpOnly** para sesiones
- ✅ **HTTPS** en producción
- ✅ **SameSite** configurado correctamente
- ✅ **CORS** restrictivo
- ✅ **Rate Limiting** en endpoints

## 📞 Troubleshooting

Si después de implementar esto aún tienes problemas:

1. **Verificar logs de Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verificar configuración:**
   ```bash
   php artisan config:show cors
   php artisan config:show sanctum
   php artisan config:show session
   ```

3. **Limpiar cache:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

4. **Verificar en navegador:**
   - DevTools → Application → Cookies
   - Debe existir cookie `XSRF-TOKEN`
   - Debe existir cookie de sesión (ej: `leykarin_session`)

---

**Última actualización:** 2025-09-29
**Estado:** ✅ Configuración recomendada para producción
