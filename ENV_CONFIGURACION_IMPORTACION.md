# Configuraciones .env para Importación/Exportación

## Configuraciones Básicas

```env
# Configuraciones de archivos
FILESYSTEM_DISK=public
MAX_FILE_SIZE=10240
ALLOWED_FILE_TYPES=xlsx,xls,csv
UPLOAD_PATH=uploads/inmuebles

# Configuraciones de importación
IMPORT_BATCH_SIZE=1000
IMPORT_CHUNK_SIZE=100
IMPORT_TIMEOUT=300
IMPORT_MEMORY_LIMIT=512M

# Configuraciones de validación
VALIDATION_STRICT_MODE=true
VALIDATION_SKIP_DUPLICATES=true
VALIDATION_MAX_ERRORS=10

# Configuraciones de preview
IMPORT_PREVIEW_ROWS=5

# Configuraciones de plantilla
IMPORT_TEMPLATE_FILENAME=plantilla_inmuebles.xlsx

# Configuraciones de logging
IMPORT_LOG_ENABLED=true
LOG_LEVEL=info

# Configuraciones de queue (opcional)
IMPORT_QUEUE_ENABLED=false
QUEUE_CONNECTION=sync
IMPORT_QUEUE_NAME=imports
IMPORT_RETRY_ATTEMPTS=3
IMPORT_RETRY_DELAY=60

# Configuraciones de notificaciones (opcional)
IMPORT_NOTIFICATIONS_ENABLED=false
IMPORT_NOTIFICATION_EMAIL=admin@example.com
```

## Configuraciones por Entorno

### Desarrollo
```env
FILESYSTEM_DISK=local
MAX_FILE_SIZE=5120
IMPORT_BATCH_SIZE=100
IMPORT_TIMEOUT=60
VALIDATION_STRICT_MODE=false
IMPORT_LOG_ENABLED=true
```

### Producción
```env
FILESYSTEM_DISK=s3
MAX_FILE_SIZE=20480
IMPORT_BATCH_SIZE=2000
IMPORT_TIMEOUT=600
VALIDATION_STRICT_MODE=true
IMPORT_LOG_ENABLED=true
IMPORT_QUEUE_ENABLED=true
QUEUE_CONNECTION=redis
```

### Testing
```env
FILESYSTEM_DISK=local
MAX_FILE_SIZE=1024
IMPORT_BATCH_SIZE=10
IMPORT_TIMEOUT=30
VALIDATION_STRICT_MODE=true
IMPORT_LOG_ENABLED=false
```

## Explicación de Configuraciones

### Archivos
- `FILESYSTEM_DISK`: Disco de almacenamiento (local, public, s3)
- `MAX_FILE_SIZE`: Tamaño máximo del archivo en KB
- `ALLOWED_FILE_TYPES`: Tipos de archivo permitidos separados por coma
- `UPLOAD_PATH`: Ruta donde se guardan los archivos

### Importación
- `IMPORT_BATCH_SIZE`: Número de registros procesados por lote
- `IMPORT_CHUNK_SIZE`: Número de registros leídos por chunk
- `IMPORT_TIMEOUT`: Tiempo máximo de ejecución en segundos
- `IMPORT_MEMORY_LIMIT`: Límite de memoria para el proceso

### Validación
- `VALIDATION_STRICT_MODE`: Modo estricto de validación
- `VALIDATION_SKIP_DUPLICATES`: Omitir duplicados automáticamente
- `VALIDATION_MAX_ERRORS`: Número máximo de errores a mostrar

### Preview
- `IMPORT_PREVIEW_ROWS`: Número de filas a mostrar en preview

### Plantilla
- `IMPORT_TEMPLATE_FILENAME`: Nombre del archivo de plantilla

### Logging
- `IMPORT_LOG_ENABLED`: Habilitar logging de importaciones
- `LOG_LEVEL`: Nivel de logging (debug, info, warning, error)

### Queue (Opcional)
- `IMPORT_QUEUE_ENABLED`: Habilitar procesamiento en background
- `QUEUE_CONNECTION`: Conexión de queue (sync, redis, database)
- `IMPORT_QUEUE_NAME`: Nombre de la cola de importación
- `IMPORT_RETRY_ATTEMPTS`: Número de intentos de reintento
- `IMPORT_RETRY_DELAY`: Delay entre reintentos en segundos

### Notificaciones (Opcional)
- `IMPORT_NOTIFICATIONS_ENABLED`: Habilitar notificaciones
- `IMPORT_NOTIFICATION_EMAIL`: Email para notificaciones

## Uso en el Código

Las configuraciones se acceden usando la función `config()`:

```php
// Obtener configuración con valor por defecto
$maxFileSize = config('import.max_file_size', 10240);
$allowedTypes = config('import.allowed_types', ['xlsx', 'xls', 'csv']);

// Configurar límites
ini_set('memory_limit', config('import.defaults.memory_limit', '512M'));
set_time_limit(config('import.defaults.timeout', 300));

// Validación
$maxErrors = config('import.validation.max_errors', 10);
$strictMode = config('import.validation.strict_mode', true);

// Logging
if (config('import.logging.enabled', true)) {
    Log::info('Importación iniciada');
}
```

## Beneficios

1. **Flexibilidad**: Cambiar configuraciones sin modificar código
2. **Entornos**: Diferentes configuraciones para dev/prod/test
3. **Seguridad**: Configuraciones sensibles en .env
4. **Mantenibilidad**: Centralizar configuraciones
5. **Escalabilidad**: Ajustar límites según necesidades 