# Mejoras en el Ordenamiento de Inmuebles

## Resumen de Cambios

Se han implementado mejoras significativas en el sistema de ordenamiento de inmuebles para permitir ordenamiento descendente (DESC) y ascendente (ASC) de manera robusta y segura.

## Problemas Solucionados

### 1. Error de Rutas No Definidas
- **Problema**: Error `Route [inmuebles.show] not defined` al acceder al endpoint `/api/v1/inmuebles`
- **Causa**: Referencias a rutas con nombres que no estaban definidas en las rutas API
- **Solución**: Reemplazadas todas las referencias `route()` por `url()` directas

### 2. Ordenamiento Mejorado
- **Problema**: Ordenamiento básico sin validación ni información de metadatos
- **Solución**: Implementado sistema de ordenamiento robusto con validación y metadatos

## Mejoras Implementadas

### 1. Validación de Parámetros de Ordenamiento

```php
// Validación de orden de clasificación
if (!in_array($sortOrder, ['asc', 'desc'])) {
    $sortOrder = 'desc';
}

// Validación de campos de ordenamiento
$allowedSortFields = ['id', 'numero', 'descripcion', 'calle', 'created_at', 'updated_at'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'created_at';
}
```

### 2. Método Helper para Ordenamiento

Se creó un método privado `applySorting()` para evitar duplicación de código:

```php
private function applySorting($query, Request $request): array
{
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = strtolower($request->get('sort_order', 'desc'));
    
    // Validaciones...
    $query->orderBy($sortBy, $sortOrder);
    
    return [
        'sort_by' => $sortBy,
        'sort_order' => $sortOrder,
        'allowed_sort_fields' => $allowedSortFields,
        'allowed_sort_orders' => ['asc', 'desc']
    ];
}
```

### 3. Metadatos de Ordenamiento en Respuestas

Todas las respuestas ahora incluyen información sobre el ordenamiento aplicado:

```json
{
  "meta": {
    "sorting": {
      "sort_by": "created_at",
      "sort_order": "desc",
      "allowed_sort_fields": ["id", "numero", "descripcion", "calle", "created_at", "updated_at"],
      "allowed_sort_orders": ["asc", "desc"]
    }
  }
}
```

### 4. Ordenamiento en Todos los Métodos

El ordenamiento ahora está disponible en:
- `GET /api/v1/inmuebles` (listado principal)
- `GET /api/v1/inmuebles/search` (búsqueda)
- `GET /api/v1/inmuebles/filter` (filtros avanzados)

## Parámetros de Ordenamiento

### Campos Disponibles para Ordenamiento
- `id` - ID del inmueble
- `numero` - Número del inmueble
- `descripcion` - Descripción del inmueble
- `calle` - Calle del inmueble
- `created_at` - Fecha de creación (por defecto)
- `updated_at` - Fecha de última actualización

### Ordenes Disponibles
- `asc` - Orden ascendente
- `desc` - Orden descendente (por defecto)

## Ejemplos de Uso

### Ordenamiento por Fecha de Creación (Descendente)
```bash
GET /api/v1/inmuebles?sort_by=created_at&sort_order=desc
```

### Ordenamiento por Número (Ascendente)
```bash
GET /api/v1/inmuebles?sort_by=numero&sort_order=asc
```

### Ordenamiento por Descripción (Descendente)
```bash
GET /api/v1/inmuebles?sort_by=descripcion&sort_order=desc
```

### Búsqueda con Ordenamiento
```bash
GET /api/v1/inmuebles/search?q=casa&sort_by=created_at&sort_order=desc
```

### Filtros con Ordenamiento
```bash
GET /api/v1/inmuebles/filter?calle=españa&sort_by=numero&sort_order=asc
```

## Comando de Prueba

Se creó un comando para probar el ordenamiento:

```bash
# Ordenamiento descendente por fecha de creación
php artisan test:inmuebles-sorting --sort_by=created_at --sort_order=desc

# Ordenamiento ascendente por número
php artisan test:inmuebles-sorting --sort_by=numero --sort_order=asc

# Ordenamiento descendente por descripción
php artisan test:inmuebles-sorting --sort_by=descripcion --sort_order=desc
```

## Beneficios

1. **Flexibilidad**: Los usuarios pueden ordenar por cualquier campo relevante
2. **Consistencia**: Ordenamiento disponible en todos los endpoints de listado
3. **Seguridad**: Validación de parámetros para prevenir inyección SQL
4. **Información**: Metadatos claros sobre el ordenamiento aplicado
5. **Mantenibilidad**: Código DRY con método helper reutilizable
6. **Compatibilidad**: Mantiene compatibilidad con parámetros existentes

## Archivos Modificados

- `app/Http/Controllers/InmuebleController.php` - Lógica principal de ordenamiento
- `app/Http/Resources/InmuebleResource.php` - Corrección de rutas
- `app/Console/Commands/TestInmueblesSorting.php` - Comando de prueba (nuevo)

## Estado Actual

✅ **Completado**: Sistema de ordenamiento robusto implementado
✅ **Completado**: Validación de parámetros
✅ **Completado**: Metadatos en respuestas
✅ **Completado**: Comando de prueba
✅ **Completado**: Corrección de errores de rutas

El sistema ahora permite ordenamiento descendente (DESC) y ascendente (ASC) de manera segura y eficiente. 