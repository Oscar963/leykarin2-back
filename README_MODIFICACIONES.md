# Sistema de Modificaciones - Planes de Compra

## Descripción

El sistema de modificaciones permite gestionar las modificaciones que se realizan a los planes de compra. Cada modificación está asociada a un plan de compra específico y mantiene un historial completo de todas las acciones realizadas.

## Características

- **Relación uno a muchos**: Un plan de compra puede tener muchas modificaciones
- **Numeración automática**: Cada modificación se numera automáticamente por plan de compra
- **Estados**: Las modificaciones pueden tener diferentes estados (activa, inactiva, pendiente, aprobada, rechazada)
- **Historial completo**: Se registra todo el historial de acciones en una tabla separada
- **Permisos**: Sistema de permisos integrado con roles y usuarios
- **API REST**: Endpoints completos para gestión CRUD

## Estructura de la Base de Datos

### Tabla `modifications`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único de la modificación |
| `modification_number` | integer | Número de modificación (secuencial por plan) |
| `date` | date | Fecha de la modificación |
| `reason` | text | Motivo de la modificación |
| `status` | string | Estado (active, inactive, pending, approved, rejected) |
| `purchase_plan_id` | bigint | ID del plan de compra asociado |
| `created_by` | bigint | ID del usuario que creó la modificación |
| `updated_by` | bigint | ID del usuario que actualizó la modificación |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Tabla `modification_histories`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único del registro de historial |
| `modification_id` | bigint | ID de la modificación |
| `action` | string | Acción realizada (create, update, delete, status_change) |
| `description` | text | Descripción de la acción |
| `details` | json | Detalles adicionales en formato JSON |
| `user_id` | bigint | ID del usuario que realizó la acción |
| `date` | timestamp | Fecha y hora de la acción |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

## Estados de las Modificaciones

- **active**: Modificación activa
- **inactive**: Modificación inactiva
- **pending**: Modificación pendiente de revisión
- **approved**: Modificación aprobada
- **rejected**: Modificación rechazada

## Instalación

Para instalar el sistema de modificaciones, ejecuta el siguiente comando:

```bash
php artisan modifications:install
```

Este comando ejecutará:
1. Las migraciones necesarias
2. El seeder de permisos
3. Limpieza de caché

## Endpoints de la API

### Listar modificaciones
```
GET /api/modifications
```

**Parámetros de consulta:**
- `query`: Término de búsqueda (opcional)
- `per_page`: Elementos por página (opcional, default: 15)

### Obtener modificaciones por plan de compra
```
GET /api/purchase-plans/{purchasePlanId}/modifications
```

### Crear modificación
```
POST /api/modifications
```

**Body:**
```json
{
    "purchase_plan_id": 1,
    "date": "2024-01-15",
    "reason": "Cambio en las especificaciones técnicas",
    "status": "pending"
}
```

### Obtener modificación específica
```
GET /api/modifications/{id}
```

### Actualizar modificación
```
PUT /api/modifications/{id}
```

### Cambiar estado de modificación
```
PUT /api/modifications/{id}/status
```

**Body:**
```json
{
    "status": "approved",
    "comment": "Aprobada por el director"
}
```

### Eliminar modificación
```
DELETE /api/modifications/{id}
```

### Obtener estados disponibles
```
GET /api/modifications/statuses
```

## Permisos

El sistema incluye los siguientes permisos:

- `modifications.list`: Ver lista de modificaciones
- `modifications.create`: Crear modificaciones
- `modifications.show`: Ver detalles de modificación
- `modifications.edit`: Editar modificaciones
- `modifications.delete`: Eliminar modificaciones
- `modifications.update_status`: Cambiar estado de modificaciones

### Asignación por Roles

- **Administrador del Sistema**: Todos los permisos
- **Administrador Municipal**: Todos los permisos
- **Director**: Listar, crear, ver, editar y cambiar estado
- **Subrogante de Director**: Listar, crear, ver, editar y cambiar estado
- **Visador**: Listar, ver y cambiar estado
- **Usuario**: Solo listar y ver

## Uso en el Código

### Crear una modificación

```php
use App\Services\ModificationService;

$modificationService = new ModificationService();

$modification = $modificationService->createModification([
    'purchase_plan_id' => 1,
    'date' => '2024-01-15',
    'reason' => 'Cambio en especificaciones',
    'status' => 'pending'
]);
```

### Obtener modificaciones de un plan

```php
use App\Models\PurchasePlan;

$purchasePlan = PurchasePlan::find(1);
$modifications = $purchasePlan->modifications;
```

### Cambiar estado

```php
$modificationService->changeModificationStatus(
    $modificationId,
    'approved',
    'Aprobada por el director'
);
```

### Obtener historial

```php
use App\Models\Modification;

$modification = Modification::find(1);
$history = $modification->history;
```

## Factory para Testing

```php
use App\Models\Modification;

// Crear modificación básica
$modification = Modification::factory()->create();

// Crear modificación activa
$modification = Modification::factory()->active()->create();

// Crear modificación para un plan específico
$modification = Modification::factory()
    ->forPurchasePlan($purchasePlan)
    ->create();
```

## Validaciones

- La fecha es obligatoria y debe tener formato válido
- El motivo es obligatorio y máximo 1000 caracteres
- El plan de compra debe existir
- El estado debe ser uno de los valores permitidos

## Notas Importantes

1. **Numeración automática**: El número de modificación se genera automáticamente por plan de compra
2. **Historial automático**: Todas las acciones se registran automáticamente en el historial
3. **Transacciones**: Todas las operaciones están protegidas con transacciones de base de datos
4. **Permisos**: El sistema respeta los permisos configurados para cada rol
5. **Relaciones**: Las modificaciones están fuertemente acopladas a los planes de compra

## Ejemplos de Respuesta de la API

### Lista de modificaciones
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "modification_number": 1,
            "date": "2024-01-15",
            "reason": "Cambio en especificaciones",
            "status": "pending",
            "status_label": "Pendiente",
            "purchase_plan_id": 1,
            "created_by": 1,
            "updated_by": null,
            "created_at": "2024-01-15 10:30:00",
            "updated_at": "2024-01-15 10:30:00",
            "purchase_plan": {
                "id": 1,
                "name": "Plan de Compra 2024",
                "year": 2024,
                "direction": {
                    "id": 1,
                    "name": "Dirección de Obras"
                }
            },
            "created_by_user": {
                "id": 1,
                "name": "Juan Pérez",
                "email": "juan@example.com"
            },
            "is_active": false,
            "is_pending": true,
            "is_approved": false,
            "is_rejected": false
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

### Estadísticas por plan de compra
```json
{
    "success": true,
    "data": [...],
    "stats": {
        "total": 5,
        "active": 2,
        "pending": 1,
        "approved": 1,
        "rejected": 1,
        "inactive": 0
    }
}
``` 