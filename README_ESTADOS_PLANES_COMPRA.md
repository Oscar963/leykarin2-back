# Migración de Estados de Planes de Compra

## Resumen de Cambios

Se ha modificado la estructura de estados de planes de compra para implementar una relación muchos a muchos entre planes de compra y estados, permitiendo un historial completo de cambios de estado.

## Cambios Realizados

### 1. Nueva Tabla Pivote: `purchase_plan_statuses`

La nueva tabla incluye los siguientes campos:
- `id` - Identificador único
- `purchase_plan_id` - ID del plan de compra
- `status_purchase_plan_id` - ID del estado
- `sending_date` - Fecha de envío/cambio de estado
- `sending_comment` - Comentario del cambio de estado 
- `created_by` - Usuario que realizó el cambio
- `created_at` y `updated_at` - Timestamps

### 2. Modelos Actualizados

#### `PurchasePlan`
- Eliminada la relación directa `status_purchase_plan_id`
- Agregadas nuevas relaciones:
  - `statuses()` - Todos los estados del plan
  - `currentStatus()` - Estado actual
  - `statusHistory()` - Historial completo de estados
- Nuevos métodos:
  - `getCurrentStatus()` - Obtiene el estado actual
  - `getCurrentStatusId()` - Obtiene el ID del estado actual
  - `getCurrentStatusName()` - Obtiene el nombre del estado actual

#### `StatusPurchasePlan`
- Agregada relación `purchasePlans()` para obtener todos los planes con este estado

#### Nuevo Modelo: `PurchasePlanStatus`
- Maneja la relación muchos a muchos
- Incluye métodos estáticos para consultas comunes

### 3. Servicios Actualizados

#### `PurchasePlanService`
- Modificados métodos de creación y actualización de estados
- Nuevo método `createPurchasePlanStatus()` para crear registros de estado
- Los métodos ahora incluyen información adicional como montos y comentarios

#### `AnnualPurchasePlanService`
- Actualizado para usar la nueva estructura de estados
- Nuevo método `createInitialStatus()` para crear estado inicial

### 4. Controladores Actualizados

#### `PurchasePlanController`
- Método `send()` actualizado para incluir datos adicionales
- Método `updateStatus()` mejorado con validación de comentarios

#### Nuevo Controlador: `PurchasePlanStatusController`
- `getStatusHistory()` - Obtiene historial completo de estados
- `getCurrentStatus()` - Obtiene estado actual
- `store()` - Crea nuevo estado
- `show()` - Muestra estado específico

### 5. Recursos Actualizados

#### `PurchasePlanResource`
- Actualizado para usar la nueva estructura de estados
- Incluye información del estado actual y historial completo

#### Nuevo Resource: `PurchasePlanStatusResource`
- Para serializar los registros de estado

### 6. Rutas Nuevas

```php
// Historial de estados
GET /api/purchase-plans/{purchasePlanId}/status-history
GET /api/purchase-plans/{purchasePlanId}/current-status

// Gestión de estados
POST /api/purchase-plan-statuses
GET /api/purchase-plan-statuses/{id}
```

## Migración de Base de Datos

### Migraciones Modificadas

1. **`2025_05_12_121309_create_purchase_plans_table.php`**
   - Eliminada la columna `status_purchase_plan_id`

2. **`2025_05_12_121310_create_purchase_plan_statuses_table.php`**
   - Nueva tabla para la relación muchos a muchos

### Ejecutar Migraciones

Como estás en modo desarrollo sin datos comprometidos, simplemente ejecuta:

```bash
php artisan migrate:fresh
```

O si prefieres mantener otras tablas:

```bash
php artisan migrate
```

## Uso de la Nueva API

### Enviar Plan de Compra

```javascript
const formData = new FormData();
formData.append('status_id', '2'); // Para aprobación
formData.append('sending_date', new Date().toISOString());
formData.append('sending_comment', 'Plan de compras enviado para aprobación de la administración municipal');

fetch(`/api/purchase-plans/${token}/send`, {
    method: 'POST',
    body: formData
});
```

### Obtener Historial de Estados

```javascript
fetch(`/api/purchase-plans/${purchasePlanId}/status-history`)
    .then(response => response.json())
    .then(data => {
        console.log('Historial de estados:', data.data);
    });
```

### Obtener Estado Actual

```javascript
fetch(`/api/purchase-plans/${purchasePlanId}/current-status`)
    .then(response => response.json())
    .then(data => {
        console.log('Estado actual:', data.data);
    });
```

## Beneficios de la Nueva Estructura

1. **Historial Completo**: Se mantiene un registro de todos los cambios de estado
2. **Información Contextual**: Cada cambio incluye información del plan en ese momento
3. **Trazabilidad**: Se puede rastrear quién realizó cada cambio y cuándo
4. **Flexibilidad**: Permite múltiples estados por plan de compra
5. **Auditoría**: Facilita la auditoría y cumplimiento normativo

## Consideraciones de Compatibilidad

- Los endpoints existentes siguen funcionando
- La información del estado actual se obtiene del registro más reciente
- No se pierde información histórica
- Estructura más robusta para futuras funcionalidades

## Próximos Pasos

1. Ejecutar las migraciones
2. Probar los nuevos endpoints
3. Actualizar el frontend para usar la nueva estructura
4. Considerar agregar validaciones adicionales según necesidades específicas 