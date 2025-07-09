# Configuraciones .env SIN RESTRICCIONES para Importación

## Configuraciones para Eliminar Restricciones

Agrega estas configuraciones a tu archivo `.env` para eliminar todas las restricciones de importación:

```env
# ========================================
# CONFIGURACIONES SIN RESTRICCIONES
# ========================================

# Configuraciones de archivos (sin límites)
FILESYSTEM_DISK=public
MAX_FILE_SIZE=50000
ALLOWED_FILE_TYPES=xlsx,xls,csv
UPLOAD_PATH=uploads/inmuebles

# Configuraciones de importación (sin límites)
IMPORT_BATCH_SIZE=5000
IMPORT_CHUNK_SIZE=1000
IMPORT_TIMEOUT=1800
IMPORT_MEMORY_LIMIT=2G

# Configuraciones de validación (DESHABILITADAS)
VALIDATION_STRICT_MODE=false
VALIDATION_SKIP_DUPLICATES=false
VALIDATION_MAX_ERRORS=999999

# Configuraciones de preview
IMPORT_PREVIEW_ROWS=10

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

# ========================================
# CONFIGURACIONES DE SEGURIDAD (SIN LÍMITES)
# ========================================

# Rate limiting (sin límites)
IMPORT_MAX_ATTEMPTS_PER_HOUR=999999
IMPORT_DECAY_MINUTES=1
IMPORT_MAX_TOTAL_SIZE_PER_HOUR=999999999999
IMPORT_MAX_CONCURRENT_IMPORTS=999
IMPORT_CONCURRENT_TIMEOUT=1800

# ========================================
# CONFIGURACIONES DE ALMACENAMIENTO
# ========================================

# Backup storage
BACKUP_STORAGE=local

# ========================================
# CONFIGURACIONES ADICIONALES
# ========================================

# Tiempo de espera para importaciones grandes
IMPORT_LARGE_FILE_TIMEOUT=3600

# Memoria adicional para archivos grandes
IMPORT_LARGE_FILE_MEMORY=4G

# Procesamiento en lotes más grandes
IMPORT_LARGE_BATCH_SIZE=10000
IMPORT_LARGE_CHUNK_SIZE=2000
```

## Explicación de los Cambios

### ✅ **Restricciones Eliminadas:**

1. **Validación Estricta**: `VALIDATION_STRICT_MODE=false`
   - Permite importar registros sin validación estricta

2. **Duplicados**: `VALIDATION_SKIP_DUPLICATES=false`
   - Permite importar registros duplicados

3. **Límite de Errores**: `VALIDATION_MAX_ERRORS=999999`
   - Permite mostrar todos los errores sin límite

4. **Tamaño de Archivo**: `MAX_FILE_SIZE=50000`
   - Aumentado a 50MB

5. **Memoria**: `IMPORT_MEMORY_LIMIT=2G`
   - Aumentado a 2GB

6. **Tiempo**: `IMPORT_TIMEOUT=1800`
   - Aumentado a 30 minutos

7. **Lotes**: `IMPORT_BATCH_SIZE=5000`
   - Aumentado para procesar más registros por lote

### ⚠️ **Consideraciones:**

- **Rendimiento**: Con estas configuraciones, el sistema puede usar más recursos
- **Memoria**: Asegúrate de que tu servidor tenga suficiente RAM
- **Tiempo**: Las importaciones grandes pueden tomar más tiempo
- **Duplicados**: Se importarán registros duplicados sin verificación

### 🔧 **Para Aplicar los Cambios:**

1. Agrega estas configuraciones a tu archivo `.env`
2. Limpia la caché de configuración:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
3. Reinicia el servidor si es necesario

### 📊 **Resultado Esperado:**

Con estas configuraciones, deberías poder importar todos los 275 registros sin restricciones. El sistema:

- ✅ No verificará duplicados
- ✅ No validará campos requeridos estrictamente
- ✅ Permitirá más errores
- ✅ Usará más memoria y tiempo
- ✅ Procesará lotes más grandes 