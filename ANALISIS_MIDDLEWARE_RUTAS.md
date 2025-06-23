# 🔒 Análisis de Middleware que Protegen las Rutas

## 📋 Resumen Ejecutivo

El sistema implementa una **arquitectura de seguridad en capas** con múltiples niveles de protección:

1. **Autenticación**: Laravel Sanctum para API
2. **Autorización por Roles**: Spatie Laravel Permission
3. **Autorización por Permisos**: Permisos granulares por módulo
4. **Validación de Reglas de Negocio**: Middleware personalizados
5. **Protección de Recursos**: Validación de acceso a direcciones

## 🏗️ Arquitectura de Seguridad

### 1. **Middleware Global (Kernel.php)**

```php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \Fruitcake\Cors\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
];
```

### 2. **Middleware de API**

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 3. **Middleware de Rutas Registrados**

```php
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'direction.permission' => \App\Http\Middleware\CheckDirectionPermission::class,
    'validate.hierarchical.user' => \App\Http\Middleware\ValidateHierarchicalUserDirection::class,
    'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
];
```

## 🔐 Niveles de Protección

### **Nivel 1: Autenticación**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Todas las rutas protegidas requieren autenticación
});
```

### **Nivel 2: Autorización por Roles**
```php
Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
    // Solo administradores pueden acceder
});
```

### **Nivel 3: Autorización por Permisos**
```php
Route::middleware(['permission:purchase_plans.list'])->group(function () {
    // Solo usuarios con permiso específico
});
```

### **Nivel 4: Validación de Reglas de Negocio**
```php
Route::middleware('validate.hierarchical.user')->group(function () {
    // Valida reglas específicas del dominio
});
```

## 📋 Resumen de Cambios Implementados

### ✅ **Cambios Realizados**

1. **🔐 Autenticación y Autorización**
   - ✅ Sistema de autenticación con Sanctum
   - ✅ Roles jerárquicos implementados
   - ✅ Permisos granulares por módulo
   - ✅ Middleware personalizado para validación jerárquica

2. **👥 Gestión de Usuarios**
   - ✅ Validación de usuarios jerárquicos (una dirección)
   - ✅ Administradores pueden tener múltiples direcciones
   - ✅ Middleware `validate.hierarchical.user` implementado

3. **🏢 Gestión de Direcciones**
   - ✅ Relaciones director-dirección
   - ✅ Asignación de usuarios a direcciones
   - ✅ Validación de jerarquías

4. **📊 Planes de Compra**
   - ✅ Validación de planes únicos por dirección/año
   - ✅ Estados y flujo de trabajo
   - ✅ Historial de movimientos
   - ✅ Exportación de datos
   - ✅ **Restricción de envío: Solo Administrador del Sistema, Administrador Municipal y Director**

5. **⚙️ Configuración del Sistema**
   - ✅ Módulos de configuración protegidos para administradores
   - ✅ **Módulos `type-projects`, `unit-purchasings`, `type-purchases`, `budget-allocations` y `status-item-purchases` accesibles para todos los usuarios autenticados**
   - ✅ Gestión de estados y tipos

6. **🔍 Auditoría y Logs**
   - ✅ Logs de actividad implementados
   - ✅ Trazabilidad de cambios
   - ✅ Historial de estados

7. **🧪 Testing**
   - ✅ Tests de validación de planes únicos
   - ✅ Tests de permisos y roles
   - ✅ Tests de middleware personalizado
   - ✅ **Comando para probar permisos de envío de planes**

8. **🔄 Migración de Roles**
   - ✅ **Cambio de "Secretaría Comunal de Planificación" → "Encargado de Presupuestos"**
   - ✅ **Cambio de "Subrogante de Secretaría Comunal de Planificación" → "Subrogante de Encargado de Presupuestos"**
   - ✅ Comando de migración creado
   - ✅ Documentación actualizada

## 📊 Análisis por Módulo

### **🔑 Autenticación**
- **Rutas públicas**: `/login`, `/logout`, `/reset-password`, `/forgot-password`
- **Protección**: Sin middleware (acceso público)

### **👥 Gestión de Usuarios**
```php
Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
    Route::apiResource('users', UserController::class)->middleware('validate.hierarchical.user');
    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword']);
});
```
- **Protección**: Roles + Validación jerárquica
- **Permisos requeridos**: Solo administradores

### **📋 Planes de Compra**
```php
// Listar y gestionar planes
Route::middleware(['permission:purchase_plans.list'])->group(function () {
    Route::apiResource('purchase-plans', PurchasePlanController::class);
    Route::get('purchase-plans/year/{year}', [PurchasePlanController::class, 'showByYear']);
    Route::get('purchase-plans/available-directions', [PurchasePlanController::class, 'getAvailableDirections']);
});

// Subir archivos
Route::middleware(['permission:purchase_plans.create'])->group(function () {
    Route::post('purchase-plans/upload/decreto', [PurchasePlanController::class, 'uploadDecreto']);
});

// Aprobar/rechazar planes
Route::middleware(['permission:purchase_plans.approve'])->group(function () {
    Route::put('purchase-plans/status/{id}', [PurchasePlanController::class, 'updateStatus']);
});

// Enviar planes (solo administradores y directores)
Route::middleware(['permission:purchase_plans.send', 'can.send.purchase.plan'])->group(function () {
    Route::post('purchase-plans/{token}/send', [PurchasePlanController::class, 'send']);
});
```
- **Protección**: Permisos + Middleware personalizado para envío
- **Permisos**: `list`, `create`, `approve`, `send`
- **Restricción de envío**: Solo Administrador del Sistema, Administrador Municipal y Director

### **🏗️ Proyectos**
```php
// Listar y gestionar proyectos
Route::middleware(['permission:projects.list'])->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/purchase-plan/{purchasePlanId}/index', [ProjectController::class, 'indexByPurchasePlan']);
});

// Verificación de proyectos
Route::middleware(['permission:projects.verification'])->group(function () {
    Route::post('projects/verification', [ProjectController::class, 'verification']);
    Route::get('projects/verification/project/{projectId}/index', [ProjectController::class, 'showVerificationProject']);
});
```
- **Protección**: Permisos específicos por funcionalidad
- **Permisos**: `list`, `verification`

### **📦 Items de Compra**
```php
// Listar y gestionar items
Route::middleware(['permission:item_purchases.list'])->group(function () {
    Route::apiResource('item-purchases', ItemPurchaseController::class);
    Route::get('item-purchases/export/{project_id}', [ItemPurchaseController::class, 'export']);
});

// Actualizar estados
Route::middleware(['permission:item_purchases.update_status'])->group(function () {
    Route::put('item-purchases/{id}/status', [ItemPurchaseController::class, 'updateStatus']);
});
```
- **Protección**: Permisos separados para gestión y estados
- **Permisos**: `list`, `update_status`

### **🏢 Direcciones**
```php
// Solo administradores
Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
    Route::apiResource('directions', DirectionController::class);
});

// Listar información
Route::middleware(['permission:directions.list'])->group(function () {
    Route::get('directions/{direction}/director', [DirectionController::class, 'getDirector']);
    Route::get('directions/{direction}/users', [DirectionController::class, 'getUsers']);
});

// Editar relaciones
Route::middleware(['permission:directions.edit'])->group(function () {
    Route::post('directions/{direction}/assign-director', [DirectionController::class, 'assignDirector']);
    Route::post('directions/{direction}/assign-users', [DirectionController::class, 'assignUsers'])->middleware('validate.hierarchical.user');
});
```
- **Protección**: Roles + Permisos + Validación jerárquica
- **Permisos**: `list`, `edit`

### **⚙️ Configuración del Sistema**
```php
// Solo administradores
Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
    Route::apiResource('status-purchase-plans', StatusPurchasePlanController::class);
    Route::apiResource('directions', DirectionController::class);
});

// Todos los usuarios autenticados
Route::apiResource('type-projects', TypeProjectController::class);
Route::apiResource('unit-purchasings', UnitPurchasingController::class);
Route::apiResource('type-purchases', TypePurchaseController::class);
Route::apiResource('budget-allocations', BudgetAllocationController::class);
Route::apiResource('status-item-purchases', StatusItemPurchaseController::class);
```
- **Protección**: Solo autenticación (sin restricciones adicionales)
- **Acceso**: Todos los usuarios autenticados

## 🛡️ Middleware Personalizados

### **1. ValidateHierarchicalUserDirection**

**Propósito**: Valida reglas de negocio para usuarios jerárquicos

**Funcionalidades**:
- ✅ Valida que usuarios jerárquicos solo pertenezcan a una dirección
- ✅ Permite múltiples direcciones solo a administradores y secretaría comunal
- ✅ Previene asignaciones incorrectas en creación/edición de usuarios

**Rutas protegidas**:
```php
'directions.assign-users',
'directions.assign-director', 
'users.store',
'users.update'
```

**Lógica de validación**:
```php
// Usuarios jerárquicos: Director, Subrogante de Director, Jefatura, Subrogante de Jefatura
// Solo pueden pertenecer a UNA dirección

// Usuarios multi-dirección: Administradores, Encargado de Presupuestos
// Pueden pertenecer a MÚLTIPLES direcciones
```

### **2. CheckDirectionPermission**

**Propósito**: Verifica permisos específicos y acceso a direcciones

**Funcionalidades**:
- ✅ Permite todo a administradores del sistema
- ✅ Permite todo a administradores municipales
- ✅ Verifica permisos específicos para otros usuarios
- ✅ Valida acceso a direcciones específicas

**Lógica de validación**:
```php
if ($user->hasRole('Administrador del Sistema')) {
    return $next($request); // Acceso total
}

if ($user->hasRole('Administrador Municipal')) {
    return $next($request); // Acceso total
}

if (!$user->can($permission)) {
    return response()->json(['message' => 'No tienes permisos'], 403);
}

// Verificar acceso a dirección específica
if (!$user->directions()->where('direction_id', $directionId)->exists()) {
    return response()->json(['message' => 'No tienes permisos para esta dirección'], 403);
}
```

### **3. CanSendPurchasePlan**

**Propósito**: Restringe el envío de planes de compra solo a roles específicos

**Funcionalidades**:
- ✅ Solo permite envío a Administrador del Sistema
- ✅ Solo permite envío a Administrador Municipal
- ✅ Solo permite envío a Director
- ✅ Bloquea envío a otros roles (SECPLAN, Jefatura, etc.)

**Rutas protegidas**:
```php
'purchase-plans.send'
```

**Lógica de validación**:
```php
$allowedRoles = [
    'Administrador del Sistema',
    'Administrador Municipal', 
    'Director'
];

if (!$user->hasAnyRole($allowedRoles)) {
    return response()->json([
        'message' => 'Solo los administradores del sistema, administradores municipales y directores pueden enviar planes de compra para aprobación.',
        'user_roles' => $user->getRoleNames()->toArray(),
        'allowed_roles' => $allowedRoles
    ], 403);
}
```

### **4. 🎯 Encargado de Presupuestos**
- **Descripción**: Gestión de planes de compra municipal
- **Jerarquía**: Nivel de planificación comunal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Gestión completa de planes y reportes

### **5. 🏢 Director**
- **Descripción**: Gestión de su dirección específica
- **Jerarquía**: Nivel directivo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Gestión completa de su dirección

### **6. 🔄 Subrogante de Director**
- **Descripción**: Funciones del director en ausencia
- **Jerarquía**: Nivel directivo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Mismos que Director

### **7. 📋 Jefatura**
- **Descripción**: Gestión operativa de proyectos
- **Jerarquía**: Nivel operativo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Gestión de proyectos e items

### **8. 🔄 Subrogante de Jefatura**
- **Descripción**: Funciones de jefatura en ausencia
- **Jerarquía**: Nivel operativo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Mismos que Jefatura

### **9. 🔄 Subrogante de Encargado de Presupuestos**
- **Descripción**: Funciones de Encargado de Presupuestos en ausencia
- **Jerarquía**: Nivel de planificación comunal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Mismos que Encargado de Presupuestos

## 📈 Matriz de Permisos por Rol

| Rol | Planes de Compra | Envío Planes | Proyectos | Items | Direcciones | Configuración | Type Projects | Unit Purchasings | Type Purchases | Budget Allocations | Status Items |
|-----|------------------|--------------|-----------|-------|-------------|---------------|---------------|------------------|----------------|-------------------|--------------|
| **Administrador del Sistema** | ✅ Total | ✅ Enviar | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Administrador Municipal** | ✅ Total | ✅ Enviar | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Director** | 🔒 Limitado | ✅ Enviar | 🔒 Limitado | 🔒 Limitado | 🔒 Solo su dirección | ❌ Sin acceso | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Subrogante de Director** | 🔒 Limitado | ❌ Sin envío | 🔒 Limitado | 🔒 Limitado | 🔒 Solo su dirección | ❌ Sin acceso | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Jefatura** | 🔒 Limitado | ❌ Sin envío | 🔒 Limitado | 🔒 Limitado | 🔒 Solo su dirección | ❌ Sin acceso | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Subrogante de Jefatura** | 🔒 Limitado | ❌ Sin envío | 🔒 Limitado | 🔒 Limitado | 🔒 Solo su dirección | ❌ Sin acceso | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Encargado de Presupuestos** | 🔒 Limitado | ❌ Sin envío | 🔒 Limitado | 🔒 Limitado | 🔒 Múltiples direcciones | ❌ Sin acceso | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |

## 🔍 Permisos Granulares

### **Planes de Compra**
- `purchase_plans.list` - Listar planes
- `purchase_plans.create` - Crear planes
- `purchase_plans.edit` - Editar planes
- `purchase_plans.delete` - Eliminar planes
- `purchase_plans.approve` - Aprobar/rechazar planes
- `purchase_plans.send` - Enviar planes
- `purchase_plans.upload_decreto` - Subir decretos

### **Proyectos**
- `projects.list` - Listar proyectos
- `projects.create` - Crear proyectos
- `projects.edit` - Editar proyectos
- `projects.delete` - Eliminar proyectos
- `projects.verification` - Verificar proyectos

### **Items de Compra**
- `item_purchases.list` - Listar items
- `item_purchases.create` - Crear items
- `item_purchases.edit` - Editar items
- `item_purchases.update_status`

### **2. Usuarios Multi-Dirección**
```php
const MULTI_DIRECTION_ROLES = [
    'Administrador del Sistema',
    'Administrador Municipal',
    'Encargado de Presupuestos',
    'Subrogante de Encargado de Presupuestos'
];
```

**Regla**: Estos usuarios pueden pertenecer a **MÚLTIPLES direcciones**