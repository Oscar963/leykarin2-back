# Código Angular para Autenticación con Laravel Sanctum

## 🔧 Configuración del Environment

```typescript
// environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  sanctumUrl: 'http://localhost:8000/sanctum/csrf-cookie',
  baseUrl: 'http://localhost:8000'
};

// environments/environment.prod.ts
export const environment = {
  production: true,
  apiUrl: 'https://dev.imaarica.cl/api',
  sanctumUrl: 'https://dev.imaarica.cl/sanctum/csrf-cookie',
  baseUrl: 'https://dev.imaarica.cl'
};
```

## 🛡️ Interceptor HTTP Corregido

```typescript
// interceptors/auth.interceptor.ts
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // IMPORTANTE: Asegurar que withCredentials esté en true para todas las peticiones
    request = request.clone({
      withCredentials: true
    });

    return next.handle(request).pipe(
      catchError((error: HttpErrorResponse) => {
        if (error.status === 401) {
          // Usuario no autenticado
          this.authService.logout();
          this.router.navigate(['/login']);
        } else if (error.status === 403) {
          // Usuario no autorizado
          console.error('Acceso denegado:', error.error.message);
        }
        return throwError(() => error);
      })
    );
  }
}
```

## 🔐 Servicio de Autenticación

```typescript
// services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  paternal_surname: string;
  maternal_surname: string;
  rut: string;
  email: string;
  status: boolean;
  direction: string | null;
  direction_id: number | null;
  roles: string[];
  permissions: string[];
}

export interface LoginRequest {
  rut: string;
  password: string;
  remember?: boolean;
}

export interface LoginResponse {
  message: string;
  user: {
    name: string;
    email: string;
  };
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;
  private isAuthenticatedSubject: BehaviorSubject<boolean>;

  constructor(private http: HttpClient) {
    this.currentUserSubject = new BehaviorSubject<User | null>(
      JSON.parse(localStorage.getItem('currentUser') || 'null')
    );
    this.currentUser = this.currentUserSubject.asObservable();
    this.isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  }

  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  public get isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  /**
   * Paso 1: Obtener CSRF token de Sanctum
   */
  getCsrfToken(): Observable<any> {
    console.log('🔐 Obteniendo CSRF token de:', environment.sanctumUrl);
    return this.http.get(environment.sanctumUrl, {
      withCredentials: true
    }).pipe(
      tap(() => console.log('✅ CSRF token obtenido correctamente')),
      catchError(error => {
        console.error('❌ Error obteniendo CSRF token:', error);
        return throwError(() => error);
      })
    );
  }

  /**
   * Paso 2: Login con credenciales
   */
  login(credentials: LoginRequest): Observable<LoginResponse> {
    console.log('🔐 Iniciando login con:', credentials.rut);
    
    return this.http.post<LoginResponse>(`${environment.apiUrl}/login`, credentials, {
      withCredentials: true
    }).pipe(
      tap(response => {
        console.log('✅ Login exitoso:', response.message);
        // Guardar información básica del usuario
        localStorage.setItem('userBasicInfo', JSON.stringify(response.user));
      }),
      catchError(error => {
        console.error('❌ Error en login:', error);
        return throwError(() => error);
      })
    );
  }

  /**
   * Paso 3: Obtener datos completos del usuario
   */
  getUser(): Observable<User> {
    console.log('👤 Obteniendo datos completos del usuario');
    
    return this.http.get<{data: User}>(`${environment.apiUrl}/user`, {
      withCredentials: true
    }).pipe(
      map(response => response.data),
      tap(user => {
        console.log('✅ Usuario obtenido:', user.name);
        localStorage.setItem('currentUser', JSON.stringify(user));
        this.currentUserSubject.next(user);
        this.isAuthenticatedSubject.next(true);
      }),
      catchError(error => {
        console.error('❌ Error obteniendo usuario:', error);
        this.logout();
        return throwError(() => error);
      })
    );
  }

  /**
   * Flujo completo de autenticación
   */
  authenticate(credentials: LoginRequest): Observable<User> {
    console.log('🚀 Iniciando flujo completo de autenticación');
    
    return this.getCsrfToken().pipe(
      // Después de obtener CSRF token, hacer login
      switchMap(() => this.login(credentials)),
      // Después del login exitoso, obtener datos del usuario
      switchMap(() => this.getUser())
    );
  }

  /**
   * Verificar si está autenticado
   */
  checkAuthStatus(): Observable<boolean> {
    console.log('🔍 Verificando estado de autenticación');
    
    return this.http.get<{isAuthenticated: boolean}>(`${environment.apiUrl}/isAuthenticated`, {
      withCredentials: true
    }).pipe(
      map(response => response.isAuthenticated),
      tap(isAuth => {
        console.log('✅ Estado de autenticación:', isAuth);
        this.isAuthenticatedSubject.next(isAuth);
        
        if (!isAuth) {
          this.logout();
        }
      }),
      catchError(error => {
        console.error('❌ Error verificando autenticación:', error);
        this.logout();
        return of(false);
      })
    );
  }

  /**
   * Logout
   */
  logout(): void {
    console.log('🚪 Cerrando sesión');
    
    // Llamar al endpoint de logout
    this.http.post(`${environment.apiUrl}/logout`, {}, {
      withCredentials: true
    }).subscribe({
      next: () => console.log('✅ Logout exitoso'),
      error: (error) => console.error('❌ Error en logout:', error)
    });

    // Limpiar datos locales
    localStorage.removeItem('currentUser');
    localStorage.removeItem('userBasicInfo');
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
  }

  /**
   * Verificar permisos
   */
  hasPermission(permission: string): boolean {
    const user = this.currentUserValue;
    return user?.permissions.includes(permission) || false;
  }

  /**
   * Verificar rol
   */
  hasRole(role: string): boolean {
    const user = this.currentUserValue;
    return user?.roles.includes(role) || false;
  }

  /**
   * Verificar si tiene alguno de los roles
   */
  hasAnyRole(roles: string[]): boolean {
    const user = this.currentUserValue;
    return user?.roles.some(role => roles.includes(role)) || false;
  }

  /**
   * Verificar si tiene alguno de los permisos
   */
  hasAnyPermission(permissions: string[]): boolean {
    const user = this.currentUserValue;
    return user?.permissions.some(permission => permissions.includes(permission)) || false;
  }
}
```

## 🎯 Componente de Login

```typescript
// components/login/login.component.ts
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  template: `
    <div class="login-container">
      <form [formGroup]="loginForm" (ngSubmit)="onSubmit()">
        <h2>Iniciar Sesión</h2>
        
        <div class="form-group">
          <label for="rut">RUT</label>
          <input 
            type="text" 
            id="rut" 
            formControlName="rut" 
            placeholder="12345678-9"
            [class.error]="loginForm.get('rut')?.invalid && loginForm.get('rut')?.touched"
          >
          <div class="error-message" *ngIf="loginForm.get('rut')?.invalid && loginForm.get('rut')?.touched">
            RUT es requerido
          </div>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input 
            type="password" 
            id="password" 
            formControlName="password"
            [class.error]="loginForm.get('password')?.invalid && loginForm.get('password')?.touched"
          >
          <div class="error-message" *ngIf="loginForm.get('password')?.invalid && loginForm.get('password')?.touched">
            Contraseña es requerida
          </div>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" formControlName="remember">
            Recordarme
          </label>
        </div>

        <button type="submit" [disabled]="loginForm.invalid || isLoading">
          {{ isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>

        <div class="error-message" *ngIf="errorMessage">
          {{ errorMessage }}
        </div>
      </form>
    </div>
  `,
  styles: [`
    .login-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    
    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    input.error {
      border-color: #dc3545;
    }
    
    .error-message {
      color: #dc3545;
      font-size: 12px;
      margin-top: 5px;
    }
    
    button {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    
    button:disabled {
      background-color: #6c757d;
      cursor: not-allowed;
    }
  `]
})
export class LoginComponent {
  loginForm: FormGroup;
  isLoading = false;
  errorMessage = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      rut: ['', [Validators.required]],
      password: ['', [Validators.required]],
      remember: [false]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.isLoading = true;
      this.errorMessage = '';

      console.log('🚀 Iniciando proceso de login...');

      this.authService.authenticate(this.loginForm.value).subscribe({
        next: (user) => {
          console.log('✅ Login exitoso, usuario:', user.name);
          this.isLoading = false;
          
          // Redirigir al dashboard
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          console.error('❌ Error en login:', error);
          this.isLoading = false;
          
          if (error.status === 422) {
            // Error de validación
            const errors = error.error.errors;
            if (errors.rut) {
              this.errorMessage = errors.rut[0];
            } else if (errors.password) {
              this.errorMessage = errors.password[0];
            } else {
              this.errorMessage = 'Credenciales inválidas';
            }
          } else if (error.status === 401) {
            this.errorMessage = 'Credenciales incorrectas';
          } else {
            this.errorMessage = 'Error de conexión. Intente nuevamente.';
          }
        }
      });
    }
  }
}
```

## 🔄 App Module Configuration

```typescript
// app.module.ts
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

import { AppComponent } from './app.component';
import { LoginComponent } from './components/login/login.component';
import { AuthInterceptor } from './interceptors/auth.interceptor';

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    ReactiveFormsModule,
    RouterModule.forRoot([
      { path: 'login', component: LoginComponent },
      { path: '', redirectTo: '/login', pathMatch: 'full' }
    ])
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
```

## 🧪 Prueba Manual del Flujo

Para probar manualmente el flujo de autenticación, puedes usar este código en la consola del navegador:

```javascript
// 1. Limpiar cookies (ejecutar en consola del navegador)
document.cookie.split(";").forEach(function(c) { 
  document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
});

// 2. Probar el flujo completo
async function testAuthFlow() {
  const baseUrl = 'https://dev.imaarica.cl';
  
  try {
    console.log('1. Obteniendo CSRF token...');
    const csrfResponse = await fetch(`${baseUrl}/sanctum/csrf-cookie`, {
      method: 'GET',
      credentials: 'include'
    });
    console.log('CSRF Response:', csrfResponse.status);
    
    console.log('2. Haciendo login...');
    const loginResponse = await fetch(`${baseUrl}/api/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include',
      body: JSON.stringify({
        rut: '12345678-9',
        password: 'password123',
        remember: true
      })
    });
    console.log('Login Response:', loginResponse.status);
    const loginData = await loginResponse.json();
    console.log('Login Data:', loginData);
    
    console.log('3. Obteniendo usuario...');
    const userResponse = await fetch(`${baseUrl}/api/user`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      },
      credentials: 'include'
    });
    console.log('User Response:', userResponse.status);
    const userData = await userResponse.json();
    console.log('User Data:', userData);
    
  } catch (error) {
    console.error('Error:', error);
  }
}

// Ejecutar la prueba
testAuthFlow();
```

## 📋 Checklist de Verificación

### Antes de probar:
- [ ] Borrar todas las cookies del dominio `dev.imaarica.cl`
- [ ] Verificar que CORS esté configurado correctamente en Laravel
- [ ] Verificar que `supports_credentials: true` esté en la configuración CORS

### Durante la prueba:
- [ ] Verificar que `/sanctum/csrf-cookie` retorne 204
- [ ] Verificar que `/api/login` retorne 200 con datos del usuario
- [ ] Verificar que `/api/user` retorne 200 con datos completos
- [ ] Verificar que las cookies se mantengan entre peticiones

### Posibles problemas:
1. **CORS no configurado**: Error en consola sobre CORS
2. **Cookies no se mantienen**: Verificar `withCredentials: true`
3. **CSRF token no válido**: Verificar llamada a `/sanctum/csrf-cookie`
4. **Sesión no persiste**: Verificar configuración de sesión en Laravel

---

**Con este código completo, deberías poder mantener la sesión correctamente. Si hay algún problema específico, revisa los logs de la consola del navegador y los logs del servidor Laravel para identificar el punto exacto donde falla.** 