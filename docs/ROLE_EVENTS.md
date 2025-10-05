# Sistema de Eventos de Roles

## Descripción

El sistema de eventos de roles proporciona trazabilidad completa de las asignaciones y revocaciones de roles en el sistema. Cada vez que se asigna o remueve un rol de un usuario, se dispara un evento que es capturado por listeners que registran la acción en los logs de seguridad.

## Problema Resuelto

El paquete `spatie/laravel-permission` no dispara eventos nativos para asignación/revocación de roles. Este sistema implementa eventos personalizados que se disparan automáticamente cuando se modifican los roles de un usuario.

## Componentes

### 1. Eventos

#### `App\Events\RoleAssigned`
Se dispara cuando se asigna un rol a un usuario.

**Propiedades:**
- `$model`: El modelo (usuario) al que se asignó el rol
- `$role`: El rol que fue asignado

#### `App\Events\RoleRevoked`
Se dispara cuando se remueve un rol de un usuario.

**Propiedades:**
- `$model`: El modelo (usuario) del que se removió el rol
- `$role`: El rol que fue removido

### 2. Listeners

#### `App\Listeners\RoleAssignedListener`
Registra en el log de seguridad cuando se asigna un rol.

**Información registrada:**
- ID del usuario
- Email del usuario
- Nombre del rol asignado
- ID del usuario que realizó la asignación
- IP de la solicitud
- Timestamp

#### `App\Listeners\RoleRevokedListener`
Registra en el log de seguridad cuando se remueve un rol.

**Información registrada:**
- ID del usuario
- Email del usuario
- Nombre del rol removido
- ID del usuario que realizó la revocación
- IP de la solicitud
- Timestamp

### 3. Trait FiresRoleEvents

El trait `App\Traits\FiresRoleEvents` sobrescribe los métodos de Spatie Permission para disparar eventos automáticamente.

**Métodos sobrescritos:**
- `assignRole()`: Asigna rol(es) y dispara evento `RoleAssigned`
- `removeRole()`: Remueve rol y dispara evento `RoleRevoked`
- `syncRoles()`: Sincroniza roles y dispara eventos para roles agregados/removidos

## Uso

### Asignación de Roles

```php
// Asignar un rol (dispara evento automáticamente)
$user->assignRole('IMA');

// Asignar múltiples roles
$user->assignRole(['IMA', 'DISAM']);

// Sincronizar roles (dispara eventos para cambios)
$user->syncRoles(['DEMUCE']);
```

### Remoción de Roles

```php
// Remover un rol (dispara evento automáticamente)
$user->removeRole('IMA');
```

### Logs de Seguridad

Los eventos se registran automáticamente en el canal de log `security`:

```php
// storage/logs/security-YYYY-MM-DD.log
[2025-09-30 00:46:42] security.INFO: Rol asignado a usuario {"user_id":5,"user_email":"usuario@ejemplo.com","role_name":"IMA","assigned_by":1,"ip":"127.0.0.1","timestamp":"2025-09-30T00:46:42.000000Z"}

[2025-09-30 00:47:15] security.WARNING: Rol removido de usuario {"user_id":5,"user_email":"usuario@ejemplo.com","role_name":"IMA","revoked_by":1,"ip":"127.0.0.1","timestamp":"2025-09-30T00:47:15.000000Z"}
```

## Integración con el Sistema

### EventServiceProvider

Los eventos están registrados en `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    RoleAssigned::class => [
        RoleAssignedListener::class,
    ],
    
    RoleRevoked::class => [
        RoleRevokedListener::class,
    ],
];
```

### Modelo User

El modelo `User` utiliza el trait `FiresRoleEvents`:

```php
class User extends Authenticatable
{
    use HasRoles;
    use FiresRoleEvents;
    // ...
}
```

## Características de Seguridad

1. **Trazabilidad Completa**: Cada cambio de rol queda registrado con información detallada
2. **Auditoría**: Los logs incluyen quién realizó el cambio y desde qué IP
3. **Timestamps**: Registro preciso de fecha y hora de cada operación
4. **Canal Dedicado**: Logs separados en canal `security` para fácil auditoría

## Consideraciones

- Los eventos se disparan **después** de que la operación se complete exitosamente
- Si una operación de rol falla, no se disparará el evento
- El trait `FiresRoleEvents` debe estar **después** del trait `HasRoles` en el modelo
- Los eventos funcionan con todos los métodos de Spatie Permission: `assignRole()`, `removeRole()`, `syncRoles()`

## Comandos Útiles

```bash
# Ver logs de seguridad en tiempo real
tail -f storage/logs/security-$(date +%Y-%m-%d).log

# Buscar asignaciones de un rol específico
grep "IMA" storage/logs/security-*.log

# Buscar cambios de un usuario específico
grep "user_id\":5" storage/logs/security-*.log
```

## Extensión

Para agregar listeners adicionales, simplemente regístralos en el `EventServiceProvider`:

```php
protected $listen = [
    RoleAssigned::class => [
        RoleAssignedListener::class,
        NotifyAdminListener::class,  // Nuevo listener
    ],
];
```

## Troubleshooting

### Los eventos no se disparan

1. Verificar que el trait `FiresRoleEvents` esté en el modelo `User`
2. Verificar que esté **después** del trait `HasRoles`
3. Limpiar cache: `php artisan cache:clear`
4. Limpiar config: `php artisan config:clear`

### Los logs no aparecen

1. Verificar configuración del canal `security` en `config/logging.php`
2. Verificar permisos de escritura en `storage/logs/`
3. Verificar que los listeners estén registrados en `EventServiceProvider`
