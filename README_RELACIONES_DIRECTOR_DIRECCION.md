# Relaciones Director-Dirección

Este documento explica cómo funcionan las relaciones entre directores y direcciones en el sistema de planes de compra, incluyendo el sistema de permisos basado en [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/installation-laravel).

## Estructura de Relaciones

### 1. Relación Principal (director_id)
- Cada dirección tiene un `director_id` que apunta al usuario que es director de esa dirección
- Esta es una relación uno a uno: una dirección tiene un director, un director puede dirigir una dirección

### 2. Relación Muchos a Muchos (direction_user)
- Los usuarios pueden pertenecer a múltiples direcciones
- Las direcciones pueden tener múltiples usuarios
- Esta relación se maneja a través de la tabla pivote `direction_user`

## Sistema de Permisos

### Implementación de Spatie Laravel Permission

El sistema utiliza [Spatie Laravel Permission v6](https://spatie.be/docs/laravel-permission/v6/installation-laravel) para manejar roles y permisos:

#### Roles Definidos:
- **Administrador del Sistema**: Acceso completo a todo el sistema
- **Administrador Municipal**: Gestión de usuarios y configuraciones
- **Visador o de Administrador Municipal**: Revisión y aprobación de planes
- **Director**: Gestión de su dirección específica
- **Subrogante de Director**: Funciones del director en ausencia
- **Jefatura**: Gestión de proyectos y ítems
- **Subrogante de Jefatura**: Funciones de jefatura en ausencia
- **Secretaría Comunal de Planificación**: Gestión de planes de compra
- **Subrogante de Secretaría Comunal de Planificación**: Funciones de SECPLAN en ausencia

#### Permisos por Módulo:
- `view purchase plans` - Ver planes de compra
- `create purchase plans` - Crear planes de compra
- `approve purchase plans` - Aprobar planes de compra
- `view purchase plan history` - Ver historial de estados
- `create purchase plan status` - Crear estados de planes
- `view movement history` - Ver historial de movimientos
- `view projects` - Ver proyectos
- `verify projects` - Verificar proyectos
- `view item purchases` - Ver ítems de compra
- `update item purchase status` - Actualizar estado de ítems
- `view files` - Ver archivos
- `view form f1` - Ver formularios F1
- `manage directions` - Gestionar direcciones

### Middleware de Protección

#### Middleware de Roles:
```php
Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
    // Rutas solo para administradores
});
```

#### Middleware de Permisos:
```php
Route::middleware(['permission:view purchase plans'])->group(function () {
    // Rutas que requieren permiso específico
});
```

#### Middleware Personalizado:
```php
Route::middleware(['direction.permission:view purchase plans'])->group(function () {
    // Rutas que verifican permiso y dirección del usuario
});
```

### Verificaciones en Controladores

```php
// Verificar permiso específico
if (!auth()->user()->can('view purchase plans')) {
    return response()->json(['message' => 'No tienes permisos'], 403);
}

// Verificar rol
if (!auth()->user()->hasRole('Director')) {
    return response()->json(['message' => 'Rol no autorizado'], 403);
}

// Verificar múltiples roles
if (!auth()->user()->hasAnyRole(['Administrador del Sistema', 'Administrador Municipal'])) {
    return response()->json(['message' => 'Acceso denegado'], 403);
}
```

## Usuarios Creados

### Directores por Dirección

| Email | Dirección | Alias |
|-------|-----------|-------|
| director.juzgado@demo.com | 1er Juzgado, 2do Juzgado, 3er Juzgado, Administrador Municipal, Dirección de Control, Asesoría Jurídica | JUZGADOS, ADMIN, CONTROL, JURÍDICO |
| director.alcaldia@demo.com | Alcaldía | ALCALDÍA |
| director.gabinete@demo.com | Gabinete de Alcaldía | GABINETE |
| director.secplan@demo.com | Secretaría Comunal de Planificación | SECPLAN |
| director.secmunicipal@demo.com | Secretaría Municipal | SECRETARIA |
| director.daf@demo.com | Dirección de Administración y Finanzas | DAF |
| director.dimao@demo.com | Dirección de Medio Ambiente, Aseo y Ornato | DIMAO |
| director.didec@demo.com | Dirección Desarrollo Comunitario | DIDEC |
| director.dom@demo.com | Dirección de Obras Municipales | DOM |
| director.transito@demo.com | Dirección de Tránsito y Transporte | TRÁNSITO |
| director.dipreseh@demo.com | Dirección Seguridad Pública | DIPRESEH |
| director.rural@demo.com | Dirección de Desarrollo Rural | RURAL |
| director.cultura@demo.com | Dirección de Cultura | CULTURA |
| director.turismo@demo.com | Dirección de Turismo | TURISMO |
| director.disam@demo.com | Dirección de Salud Municipal | DISAM |
| director.demuce@demo.com | Departamento Municipal de Cementerios | DEMUCE |

### Usuarios de Ejemplo por Dirección

| Email | Rol | Dirección |
|-------|-----|-----------|
| usuario.daf1@demo.com | Jefatura | DAF |
| usuario.daf2@demo.com | Subrogante de Jefatura | DAF |
| usuario.dimao1@demo.com | Jefatura | DIMAO |
| usuario.dom1@demo.com | Jefatura | DOM |
| usuario.dom2@demo.com | Subrogante de Jefatura | DOM |
| usuario.didec1@demo.com | Jefatura | DIDEC |
| usuario.disam1@demo.com | Jefatura | DISAM |
| usuario.disam2@demo.com | Subrogante de Jefatura | DISAM |

## API Endpoints

### Obtener Director de una Dirección
```http
GET /api/directions/{direction}/director
```

### Obtener Usuarios de una Dirección
```http
GET /api/directions/{direction}/users
```

### Obtener Usuarios por Rol en una Dirección
```http
GET /api/directions/{direction}/users-by-role?role=Director
```

### Asignar Director a una Dirección
```http
POST /api/directions/{direction}/assign-director
Content-Type: application/json

{
    "user_id": 1
}
```

### Asignar Usuarios a una Dirección
```http
POST /api/directions/{direction}/assign-users
Content-Type: application/json

{
    "user_ids": [1, 2, 3]
}
```

### Remover Usuarios de una Dirección
```http
DELETE /api/directions/{direction}/remove-users
Content-Type: application/json

{
    "user_ids": [1, 2, 3]
}
```

### Obtener Estadísticas de Usuarios por Dirección
```http
GET /api/directions-stats/users
```

## Comandos Artisan

### Mostrar Relaciones Director-Dirección
```bash
php artisan directors:show-relations
```

Este comando muestra:
- Todas las direcciones con sus directores asignados
- Usuarios en cada dirección
- Resumen estadístico
- Directores con múltiples direcciones

## Métodos del Modelo Direction

### Obtener Usuarios por Rol
```php
$direction = Direction::find(1);
$directors = $direction->getUsersByRole('Director');
```

### Obtener Director
```php
$director = $direction->getDirector();
```

### Obtener Usuarios No Directores
```php
$nonDirectors = $direction->getNonDirectorUsers();
```

### Verificar si un Usuario Pertenece
```php
$user = User::find(1);
$belongsTo = $direction->hasUser($user);
```

### Contar Usuarios
```php
$count = $direction->getUserCount();
```

## Ejecutar Seeders

Para crear todas las relaciones:

```bash
php artisan db:seed
```

O ejecutar solo los seeders necesarios en orden:

```bash
php artisan db:seed --class=DirectionSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=DirectorDirectionRelationSeeder
```

### Orden de Ejecución Importante

1. **DirectionSeeder**: Crea las direcciones sin `director_id`
2. **UserSeeder**: Crea los usuarios (directores y otros)
3. **DirectorDirectionRelationSeeder**: Crea las relaciones entre directores y direcciones

Este orden evita errores de foreign key constraint.

## Consideraciones Importantes

1. **Un director puede dirigir múltiples direcciones**: Por ejemplo, `director.juzgado@demo.com` dirige 6 direcciones diferentes.

2. **Los usuarios pueden pertenecer a múltiples direcciones**: A través de la relación muchos a muchos.

3. **No se puede remover al director de su dirección**: El sistema protege esta relación.

4. **Solo usuarios con rol "Director" pueden ser asignados como directores**: El sistema valida esto automáticamente.

5. **Las relaciones se crean automáticamente**: Al asignar un director, se crea automáticamente la relación en la tabla pivote.

## Casos de Uso

### 1. Buscar Planes por Director
```php
$director = User::where('email', 'director.daf@demo.com')->first();
$directions = $director->directions;
$purchasePlans = PurchasePlan::whereIn('direction_id', $directions->pluck('id'))->get();
```

### 2. Obtener Todos los Usuarios de una Dirección
```php
$direction = Direction::where('alias', 'DAF')->first();
$users = $direction->users; // Incluye al director y otros usuarios
```

### 3. Filtrar por Rol en una Dirección
```php
$direction = Direction::find(1);
$jefaturas = $direction->getUsersByRole('Jefatura');
```

### 4. Verificar Permisos por Dirección
```php
$user = auth()->user();
$direction = Direction::find(1);

if ($direction->hasUser($user)) {
    // El usuario pertenece a esta dirección
    if ($user->hasRole('Director') && $direction->director_id === $user->id) {
        // Es el director de esta dirección
    }
}
``` 