# 🔧 Configuración Completa del Archivo .env

## 📋 Instrucciones de Configuración

Copia este contenido en tu archivo `.env` y ajusta los valores según tu entorno:

```env
APP_NAME="Bienes Inmuebles Backend"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bienesinmuebles
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# ========================================
# CONFIGURACIÓN DE SEGURIDAD Y COOKIES
# ========================================

# Configuración de cookies para autenticación
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
SESSION_DOMAIN=localhost

# Configuración de Sanctum para API
SANCTUM_STATEFUL_DOMAINS=localhost:4200,127.0.0.1:4200

# Configuración de CORS
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://127.0.0.1:4200
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With

# ========================================
# CONFIGURACIÓN DE RATE LIMITING
# ========================================

# Límites de rate limiting para endpoints críticos
RATE_LIMIT_LOGIN=5,1
RATE_LIMIT_RESET_PASSWORD=3,1
RATE_LIMIT_LOGOUT=10,1
RATE_LIMIT_API=60,1

# ========================================
# CONFIGURACIÓN DE LOGGING
# ========================================

# Configuración de logging de seguridad
SECURITY_LOG_CHANNEL=security
SECURITY_LOG_LEVEL=warning

# Configuración de logging de archivos
FILE_UPLOAD_LOG_CHANNEL=file_uploads
FILE_UPLOAD_LOG_LEVEL=info

# ========================================
# CONFIGURACIÓN DE VALIDACIÓN DE ARCHIVOS
# ========================================

# Tamaños máximos de archivo (en KB)
MAX_FILE_SIZE_PDF=20480
MAX_FILE_SIZE_IMAGE=5120
MAX_FILE_SIZE_DOCUMENT=10240

# Extensiones permitidas
ALLOWED_FILE_EXTENSIONS_PDF=pdf
ALLOWED_FILE_EXTENSIONS_IMAGE=jpg,jpeg,png,gif
ALLOWED_FILE_EXTENSIONS_DOCUMENT=doc,docx,pdf

# ========================================
# CONFIGURACIÓN DE RECAPTCHA
# ========================================

RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key

# ========================================
# CONFIGURACIÓN DE SWAGGER/API DOCS
# ========================================

L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_UI_DOC_EXPANSION=list
L5_SWAGGER_UI_OPERATIONS_SORTER=alpha

# ========================================
# CONFIGURACIÓN DE BASE DE DATOS
# ========================================

# Configuración adicional de MySQL
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Configuración de conexión de prueba
DB_TEST_DATABASE=bienesinmuebles_test

# ========================================
# CONFIGURACIÓN DE CACHE
# ========================================

# Configuración de cache para roles y permisos
CACHE_ROLES_TTL=3600
CACHE_PERMISSIONS_TTL=3600

# ========================================
# CONFIGURACIÓN DE QUEUES
# ========================================

# Configuración de colas para emails
QUEUE_MAIL_CONNECTION=database
QUEUE_MAIL_TABLE=jobs

# ========================================
# CONFIGURACIÓN DE STORAGE
# ========================================

# Configuración de almacenamiento de archivos
FILESYSTEM_DISK_PUBLIC=public
FILESYSTEM_DISK_PRIVATE=local

# Rutas de almacenamiento
STORAGE_PATH_DECRETOS=decretos
STORAGE_PATH_VERIFICATIONS=verifications
STORAGE_PATH_TEMPLATES=templates

# ========================================
# CONFIGURACIÓN DE NOTIFICACIONES
# ========================================

# Configuración de notificaciones por email
NOTIFICATION_MAIL_FROM_ADDRESS=noreply@bienesinmuebles.cl
NOTIFICATION_MAIL_FROM_NAME="Sistema de Bienes Inmuebles"

# ========================================
# CONFIGURACIÓN DE MONITOREO
# ========================================

# Configuración de monitoreo de seguridad
SECURITY_MONITORING_ENABLED=true
SECURITY_ALERT_EMAIL=admin@bienesinmuebles.cl

# Configuración de auditoría
AUDIT_LOG_ENABLED=true
AUDIT_LOG_RETENTION_DAYS=90

# ========================================
# CONFIGURACIÓN DE DESARROLLO
# ========================================

# Configuración para desarrollo
DEVELOPMENT_MODE=false
DEBUG_BAR_ENABLED=false

# Configuración de testing
TESTING_MODE=false
MOCK_EXTERNAL_SERVICES=true
```

## 🚀 Pasos para Configurar

### 1. **Generar Clave de Aplicación**
```bash
php artisan key:generate
```

### 2. **Configurar Base de Datos**
- Crea la base de datos `bienesinmuebles`
- Ajusta las credenciales de conexión
- Ejecuta las migraciones: `php artisan migrate`

### 3. **Configurar Almacenamiento**
```bash
php artisan storage:link
```

### 4. **Configurar CORS**
Ajusta `CORS_ALLOWED_ORIGINS` con los dominios de tu frontend

### 5. **Configurar Rate Limiting**
Los valores están optimizados para seguridad, ajusta según necesidades

### 6. **Configurar Logging**
Los canales de logging están configurados para monitoreo de seguridad

## 🔒 Configuraciones de Seguridad

### **Rate Limiting**
- **Login**: 5 intentos por minuto
- **Reset Password**: 3 intentos por minuto  
- **Logout**: 10 intentos por minuto
- **API General**: 60 requests por minuto

### **Validación de Archivos**
- **PDFs**: Máximo 20MB
- **Imágenes**: Máximo 5MB
- **Documentos**: Máximo 10MB

### **Cookies y Sesiones**
- **SameSite**: Lax (seguro para desarrollo)
- **Secure**: False (para desarrollo local)
- **Domain**: localhost

## 📊 Monitoreo y Logging

### **Canales de Log**
- **Security**: Eventos de seguridad críticos
- **File Uploads**: Subida de archivos
- **Stack**: Logs generales

### **Retención de Logs**
- **Auditoría**: 90 días
- **Seguridad**: Permanente
- **Archivos**: 30 días

## 🛠️ Configuración por Entorno

### **Desarrollo Local**
```env
APP_ENV=local
APP_DEBUG=true
SESSION_SECURE_COOKIE=false
DEVELOPMENT_MODE=true
```

### **Producción**
```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
DEVELOPMENT_MODE=false
```

### **Testing**
```env
APP_ENV=testing
APP_DEBUG=true
TESTING_MODE=true
DB_DATABASE=bienesinmuebles_test
```

## ⚠️ Variables Críticas

### **Obligatorias**
- `APP_KEY` - Generada automáticamente
- `DB_*` - Configuración de base de datos
- `CORS_ALLOWED_ORIGINS` - Dominios permitidos

### **Opcionales pero Recomendadas**
- `RECAPTCHA_*` - Para protección contra bots
- `SECURITY_ALERT_EMAIL` - Para alertas de seguridad
- `NOTIFICATION_MAIL_*` - Para notificaciones

## 🔧 Comandos Útiles

```bash
# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de rutas
php artisan route:clear

# Limpiar cache de vistas
php artisan view:clear

# Limpiar todo el cache
php artisan optimize:clear

# Verificar configuración
php artisan config:show
``` 