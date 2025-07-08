# Filtro General de Inmuebles

## Resumen de Cambios

Se ha implementado un filtro general con el parámetro `q` que permite buscar en todos los campos de texto de la tabla inmuebles, complementando los filtros específicos existentes.

## Funcionalidad Implementada

### Filtro General (Parámetro `q`)

El parámetro `q` permite realizar búsquedas generales que incluyen todos los campos de texto de la tabla inmuebles:

- `numero` - Número del inmueble
- `descripcion` - Descripción del inmueble
- `calle` - Calle del inmueble
- `numeracion` - Numeración
- `lote_sitio` - Lote o sitio
- `manzana` - Manzana
- `poblacion_villa` - Población o villa
- `foja` - Foja
- `inscripcion_numero` - Número de inscripción
- `inscripcion_anio` - Año de inscripción
- `rol_avaluo` - Rol de avalúo
- `superficie` - Superficie
- `deslinde_norte` - Deslinde norte
- `deslinde_sur` - Deslinde sur
- `deslinde_este` - Deslinde este
- `deslinde_oeste` - Deslinde oeste
- `decreto_incorporacion` - Decreto de incorporación
- `decreto_destinacion` - Decreto de destinación
- `observaciones` - Observaciones

## Endpoints con Filtro General

### 1. Listado Principal
```
GET /api/v1/inmuebles?q=españa
```

### 2. Búsqueda Específica
```
GET /api/v1/inmuebles/search?q=casa
```

### 3. Filtros Avanzados
```
GET /api/v1/inmuebles/filter?q=municipal&calle=españa
```

## Combinación de Filtros

El filtro general puede combinarse con filtros específicos:

```bash
# Búsqueda general + filtro específico
GET /api/v1/inmuebles?q=casa&calle=españa&sort_by=numero&sort_order=asc

# Búsqueda general + filtros de fecha
GET /api/v1/inmuebles/filter?q=municipal&created_at_from=2025-01-01
```

## Respuesta con Metadatos

Las respuestas incluyen información sobre los filtros aplicados:

```json
{
  "data": [...],
  "meta": {
    "filters": {
      "general_search": "casa",
      "specific_filters": {
        "calle": "españa"
      }
    },
    "sorting": {
      "sort_by": "id",
      "sort_order": "desc"
    }
  }
}
```

## Ejemplos de Uso

### Búsqueda por Número
```bash
GET /api/v1/inmuebles?q=001
```

### Búsqueda por Descripción
```bash
GET /api/v1/inmuebles?q=casa municipal
```

### Búsqueda por Calle
```bash
GET /api/v1/inmuebles?q=españa
```

### Búsqueda por Manzana
```bash
GET /api/v1/inmuebles?q=M1
```

### Búsqueda por Población
```bash
GET /api/v1/inmuebles?q=central
```

### Búsqueda por Decreto
```bash
GET /api/v1/inmuebles?q=decreto
```

## Comando de Prueba

Se creó un comando para probar el filtro general:

```bash
# Búsqueda básica
php artisan test:inmuebles-general-search "casa"

# Búsqueda con límite personalizado
php artisan test:inmuebles-general-search "españa" --limit=10

# Búsqueda por número
php artisan test:inmuebles-general-search "001"
```

## Implementación Técnica

### Método Helper

Se creó un método privado `applyGeneralSearch()` para evitar duplicación de código:

```php
private function applyGeneralSearch($query, string $searchTerm): void
{
    $query->where(function ($q) use ($searchTerm) {
        $q->where('numero', 'like', "%{$searchTerm}%")
          ->orWhere('descripcion', 'like', "%{$searchTerm}%")
          ->orWhere('calle', 'like', "%{$searchTerm}%")
          // ... todos los campos de texto
          ->orWhere('observaciones', 'like', "%{$searchTerm}%");
    });
}
```

### Uso en Controladores

El filtro general se aplica en:
- `InmuebleController@index` - Listado principal
- `InmuebleController@search` - Búsqueda específica
- `InmuebleController@filter` - Filtros avanzados

## Beneficios

1. **Búsqueda Completa**: Permite buscar en todos los campos de texto
2. **Flexibilidad**: Se puede combinar con filtros específicos
3. **Eficiencia**: Usa un solo método helper reutilizable
4. **Consistencia**: Disponible en todos los endpoints de listado
5. **Información**: Metadatos claros sobre los filtros aplicados
6. **Compatibilidad**: No afecta los filtros existentes

## Campos de Búsqueda

| Campo | Descripción | Tipo |
|-------|-------------|------|
| numero | Número del inmueble | string |
| descripcion | Descripción del inmueble | text |
| calle | Calle del inmueble | string |
| numeracion | Numeración | string |
| lote_sitio | Lote o sitio | string |
| manzana | Manzana | string |
| poblacion_villa | Población o villa | string |
| foja | Foja | string |
| inscripcion_numero | Número de inscripción | string |
| inscripcion_anio | Año de inscripción | string |
| rol_avaluo | Rol de avalúo | string |
| superficie | Superficie | string |
| deslinde_norte | Deslinde norte | string |
| deslinde_sur | Deslinde sur | string |
| deslinde_este | Deslinde este | string |
| deslinde_oeste | Deslinde oeste | string |
| decreto_incorporacion | Decreto de incorporación | string |
| decreto_destinacion | Decreto de destinación | string |
| observaciones | Observaciones | text |

## Archivos Modificados

- `app/Http/Controllers/InmuebleController.php` - Lógica principal del filtro general
- `app/Console/Commands/TestInmueblesGeneralSearch.php` - Comando de prueba (nuevo)

## Estado Actual

✅ **Completado**: Filtro general implementado
✅ **Completado**: Método helper reutilizable
✅ **Completado**: Metadatos en respuestas
✅ **Completado**: Comando de prueba
✅ **Completado**: Integración con filtros existentes

El sistema ahora permite búsquedas generales en todos los campos de texto de la tabla inmuebles de manera eficiente y flexible. 