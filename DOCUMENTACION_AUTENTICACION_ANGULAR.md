# Documentación Sistema de Autenticación Angular + Laravel API

## 📋 Configuración del Backend (Laravel)

### Endpoints de Autenticación
```
Base URL: http://localhost:8000/api

// Login
POST /api/login
Body: { rut: string, password: string, remember?: boolean }
Response: { message: string, user: { name: string, email: string } }

// Logout
POST /api/logout
Response: { message: string }

// Obtener usuario autenticado
GET /api/user
Response: {
  data: {
    id: number,
    name: string,
    paternal_surname: string,
    maternal_surname: string,
    rut: string,
    email: string,
    status: boolean,
    direction: string | null,
    direction_id: number | null,
    roles: string[],
    permissions: string[]
  }
}

// Verificar si está autenticado
GET /api/isAuthenticated
Response: { isAuthenticated: boolean }
```

### Configuración CORS (Laravel)
```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['http://localhost:4200'], // Tu dominio Angular
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

## 🎭 Sistema de Roles y Permisos

### Roles Disponibles
- `Administrador del Sistema`
- `Administrador Municipal`
- `Visador o de Administrador Municipal`
- `Director`
- `Subrogante de Director`
- `Jefatura`
- `Subrogante de Jefatura`
- `Secretaría Comunal de Planificación`
- `Subrogante de Secretaría Comunal de Planificación`

### Permisos Principales
```
// Planes de Compra
'purchase_plans.list'      // Ver planes de compra
'purchase_plans.create'    // Crear planes de compra
'purchase_plans.edit'      // Editar planes de compra
'purchase_plans.delete'    // Eliminar planes de compra
'purchase_plans.view'      // Ver detalle de plan
'purchase_plans.approve'   // Aprobar planes
'purchase_plans.reject'    // Rechazar planes
'purchase_plans.send'      // Enviar planes
'purchase_plans.export'    // Exportar planes

// Proyectos
'projects.list'           // Ver proyectos
'projects.create'         // Crear proyectos
'projects.edit'           // Editar proyectos
'projects.delete'         // Eliminar proyectos
'projects.view'           // Ver detalle de proyecto
'projects.verification'   // Verificar proyectos

// Ítems de Compra
'item_purchases.list'     // Ver ítems
'item_purchases.create'   // Crear ítems
'item_purchases.edit'     // Editar ítems
'item_purchases.update_status' // Actualizar estado

// Direcciones
'directions.list'         // Ver direcciones
'directions.create'       // Crear direcciones
'directions.edit'         // Editar direcciones
'directions.delete'       // Eliminar direcciones

// Archivos
'files.list'              // Ver archivos
'files.create'            // Crear archivos
'files.download'          // Descargar archivos

// Usuarios (solo administradores)
'users.create'            // Crear usuarios
'users.edit'              // Editar usuarios
'users.delete'            // Eliminar usuarios
```

## 📝 Estructura de Usuario

### Interfaces TypeScript
```typescript
interface User {
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

interface LoginRequest {
  rut: string;
  password: string;
  remember?: boolean;
}

interface LoginResponse {
  message: string;
  user: {
    name: string;
    email: string;
  };
}
```

## 🔄 Flujo de Autenticación

### 1. Login
```typescript
// Si usas cookies de sesión, primero obtener CSRF token
GET /sanctum/csrf-cookie

// Luego hacer login
POST /api/login
{
  "rut": "12345678-9",
  "password": "password123",
  "remember": true
}
```

### 2. Obtener Datos del Usuario
```typescript
GET /api/user
// Retorna usuario completo con roles y permisos
```

### 3. Peticiones Autenticadas
```typescript
// Todas las peticiones deben incluir credenciales
{
  withCredentials: true
}
```

### 4. Logout
```typescript
POST /api/logout
```

## 🛡️ Protección de Rutas en Angular

### Guards Necesarios
```typescript
// AuthGuard - Verificar si está autenticado
// RoleGuard - Verificar rol específico
// PermissionGuard - Verificar permiso específico
// DirectionGuard - Verificar acceso a dirección específica
```

### Directivas Necesarias
```typescript
// *hasRole="'Director'" - Mostrar si tiene rol
// *hasPermission="'purchase_plans.create'" - Mostrar si tiene permiso
// *hasAnyRole="['Director', 'Jefatura']" - Mostrar si tiene alguno de los roles
```

## 🔧 Servicios Angular Recomendados

### AuthService
```typescript
interface AuthService {
  login(credentials: LoginRequest): Observable<LoginResponse>;
  logout(): Observable<any>;
  getUser(): Observable<User>;
  isAuthenticated(): Observable<boolean>;
  hasRole(role: string): boolean;
  hasPermission(permission: string): boolean;
  hasAnyRole(roles: string[]): boolean;
  hasAnyPermission(permissions: string[]): boolean;
}
```

### PermissionService
```typescript
interface PermissionService {
  checkPermission(permission: string): boolean;
  checkRole(role: string): boolean;
  getUserPermissions(): string[];
  getUserRoles(): string[];
}
```

## ⚙️ Configuración Angular

### HTTP Interceptor
```typescript
// Agregar credenciales a todas las peticiones
// Manejar errores 401/403
// Refrescar token si es necesario
```

### Environment
```typescript
// environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  sanctumUrl: 'http://localhost:8000/sanctum/csrf-cookie'
};
```

## 💡 Casos de Uso Específicos

### Verificar Permisos en Componentes
```typescript
// En componente
if (this.authService.hasPermission('purchase_plans.create')) {
  // Mostrar botón de crear
}

// En template
<button *hasPermission="'purchase_plans.create'">Crear Plan</button>
```

### Proteger Rutas
```typescript
// En routing
{
  path: 'purchase-plans',
  component: PurchasePlansComponent,
  canActivate: [AuthGuard, PermissionGuard],
  data: { permission: 'purchase_plans.list' }
}
```

## ⚠️ Manejo de Errores

### Códigos de Error
- `401` - No autenticado
- `403` - No autorizado (sin permisos)
- `404` - Recurso no encontrado
- `422` - Error de validación

### Respuestas de Error
```typescript
{
  message: "No tienes permisos para realizar esta acción"
}
```

## 🔒 Consideraciones de Seguridad

- Usar `withCredentials: true` en todas las peticiones autenticadas
- Validar permisos tanto en frontend como backend
- No confiar solo en la UI para seguridad
- Manejar expiración de sesión
- Implementar refresh de sesión si es necesario

## 📋 Checklist de Implementación

### Backend (Laravel) ✅
- [x] Sanctum configurado
- [x] CORS configurado con `supports_credentials: true`
- [x] Endpoints de autenticación funcionando
- [x] Roles y permisos creados y asignados
- [x] Middleware de protección en rutas
- [x] Respuestas JSON estándar

### Frontend (Angular) 🔄
- [ ] Configurar environment con URLs de API
- [ ] Crear interfaces TypeScript
- [ ] Implementar AuthService
- [ ] Implementar PermissionService
- [ ] Crear guards de autenticación
- [ ] Crear directivas de permisos
- [ ] Configurar HTTP Interceptor
- [ ] Implementar manejo de errores
- [ ] Configurar routing protegido

## 🚀 Ejemplo de Uso Completo

### 1. Login en Angular
```typescript
// auth.service.ts
login(credentials: LoginRequest): Observable<LoginResponse> {
  return this.http.post<LoginResponse>(`${environment.apiUrl}/login`, credentials, {
    withCredentials: true
  });
}
```

### 2. Obtener Usuario
```typescript
getUser(): Observable<User> {
  return this.http.get<{data: User}>(`${environment.apiUrl}/user`, {
    withCredentials: true
  }).pipe(
    map(response => response.data)
  );
}
```

### 3. Verificar Permisos
```typescript
hasPermission(permission: string): boolean {
  return this.currentUser?.permissions.includes(permission) || false;
}
```

### 4. En Template
```html
<button *hasPermission="'purchase_plans.create'" (click)="createPlan()">
  Crear Plan de Compra
</button>

<div *hasRole="'Director'">
  Panel de Director
</div>
```

---

**Con esta documentación completa, cualquier desarrollador o IA podrá implementar un sistema de autenticación robusto y seguro en Angular que se integre perfectamente con tu backend Laravel.** 