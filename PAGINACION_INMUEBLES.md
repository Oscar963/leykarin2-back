    # Paginación de Inmuebles

## Estructura de Respuesta

La API de inmuebles ahora devuelve una estructura de paginación mejorada que separa los datos de los links de paginación.

### Ejemplo de Respuesta

```json
{
    "data": [
        {
            "id": 1,
            "numero": "123",
            "descripcion": "Casa en venta",
            "calle": "Av. Principal",
            // ... otros campos del inmueble
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75,
        "path": "/api/v1/inmuebles"
    },
    "links": {
        "first": "/api/v1/inmuebles?page=1",
        "last": "/api/v1/inmuebles?page=5",
        "prev": null,
        "next": "/api/v1/inmuebles?page=2",
        "current": "/api/v1/inmuebles?page=1",
        "pages": {
            "1": "/api/v1/inmuebles?page=1",
            "2": "/api/v1/inmuebles?page=2",
            "3": "/api/v1/inmuebles?page=3",
            "4": "/api/v1/inmuebles?page=4",
            "5": "/api/v1/inmuebles?page=5"
        }
    },
    "pagination_html": "<nav>...</nav>"
}
```

## Parámetros de Paginación

### Parámetros Disponibles

- `q`: Término de búsqueda general
- `show`: Número de elementos por página (por defecto: 50)
- `pagination_range`: Rango de páginas a mostrar alrededor de la página actual (por defecto: 2)

### Ejemplo de Uso

```bash
# Obtener primera página con 20 elementos por página
GET /api/v1/inmuebles?show=20

# Buscar inmuebles con paginación
GET /api/v1/inmuebles?q=casa&show=10

# Con rango de paginación personalizado
GET /api/v1/inmuebles?pagination_range=3
```

## Estructura de Links

### Links Principales

- `first`: URL de la primera página
- `last`: URL de la última página
- `prev`: URL de la página anterior (null si es la primera)
- `next`: URL de la página siguiente (null si es la última)
- `current`: URL de la página actual

### Links de Páginas

El objeto `pages` contiene los links de todas las páginas disponibles en el rango configurado:

```json
"pages": {
    "1": "/api/v1/inmuebles?page=1",
    "2": "/api/v1/inmuebles?page=2",
    "3": "/api/v1/inmuebles?page=3",
    "4": "/api/v1/inmuebles?page=4",
    "5": "/api/v1/inmuebles?page=5"
}
```

### Separadores

Cuando hay muchas páginas, se incluyen separadores (`...`) para indicar páginas omitidas:

```json
"pages": {
    "1": "/api/v1/inmuebles?page=1",
    "...": null,
    "4": "/api/v1/inmuebles?page=4",
    "5": "/api/v1/inmuebles?page=5",
    "6": "/api/v1/inmuebles?page=6",
    "...": null,
    "10": "/api/v1/inmuebles?page=10"
}
```

## Ventajas de la Nueva Estructura

1. **Separación clara**: Los datos están separados de los links de paginación
2. **Flexibilidad**: Puedes usar los links que necesites sin depender del HTML de Laravel
3. **Control total**: Acceso directo a URLs específicas de páginas
4. **Rango configurable**: Puedes ajustar cuántas páginas mostrar alrededor de la actual
5. **Compatibilidad**: Mantiene la estructura HTML de Laravel si la necesitas

## Uso en Frontend

```javascript
// Ejemplo de uso en JavaScript
fetch('/api/v1/inmuebles?show=20&pagination_range=3')
    .then(response => response.json())
    .then(data => {
        // Renderizar datos
        renderInmuebles(data.data);
        
        // Usar links de paginación
        if (data.links.next) {
            // Habilitar botón "Siguiente"
            enableNextButton(data.links.next);
        }
        
        // Renderizar páginas disponibles
        renderPagination(data.links.pages);
    });
``` 