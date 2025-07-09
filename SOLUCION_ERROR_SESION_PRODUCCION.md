# Solución para Error de Sesión en Producción

## Problema
```
"message": "Session store not set on request.",
"exception": "RuntimeException",
"file": "/home/bienesinmueblesi/auth/vendor/laravel/framework/src/Illuminate/Http/Request.php",
"line": 515
```

## Causa del Problema
El error ocurre porque:
1. Las rutas API están usando el middleware `api` que no incluye sesiones
2. Sanctum está configurado para usar el guard `web` que requiere sesiones
3. El dominio de producción `bienesinmuebles.imaarica.cl` no está en la lista de dominios stateful de Sanctum

## Soluciones Implementadas

### 1. Configuración de Sanctum
Se agregó el dominio de producción a la lista de dominios stateful en `config/sanctum.php`:

```php
'stateful' => [
    'localhost',
    'localhost:3000',
    'localhost:4200',
    '127.0.0.1',
    '127.0.0.1:8000',
    '127.0.0.1:4200',
    '::1',
    'bienesinmuebles.imaarica.cl',
    '*.imaarica.cl'
],
```

### 2. Middleware de Sesión en API
Se agregó el middleware de sesión al grupo API en `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Session\Middleware\StartSession::class, // ← Agregado
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Pasos para Aplicar en Producción

### 1. Actualizar Archivos de Configuración
```bash
# Subir los archivos modificados
git add config/sanctum.php
git add app/Http/Kernel.php
git commit -m "Fix: Agregar configuración de sesiones para API"
git push
```

### 2. Limpiar Caché en Producción
```bash
# Conectar al servidor de producción
ssh usuario@servidor

# Navegar al directorio de la aplicación
cd /home/bienesinmueblesi/auth

# Limpiar todas las cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar caché de configuración
php artisan config:cache
```

### 3. Verificar Permisos de Directorios
```bash
# Verificar que el directorio de sesiones existe y tiene permisos
ls -la storage/framework/sessions/

# Si no existe, crearlo
mkdir -p storage/framework/sessions
chmod 755 storage/framework/sessions
chown www-data:www-data storage/framework/sessions
```

### 4. Verificar Configuración de Base de Datos
```bash
# Verificar que la tabla de sesiones existe
php artisan migrate:status

# Si no está ejecutada la migración de sesiones
php artisan migrate
```

### 5. Verificar Variables de Entorno
Asegúrate de que en el archivo `.env` de producción estén configuradas:

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.imaarica.cl
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### 6. Reiniciar Servicios Web
```bash
# Reiniciar Apache/Nginx
sudo systemctl restart apache2
# o
sudo systemctl restart nginx

# Reiniciar PHP-FPM si aplica
sudo systemctl restart php8.1-fpm
```

## Verificación

### 1. Comando de Verificación
Se creó un comando personalizado para verificar la configuración:

```bash
php artisan app:check-environment-config
```

### 2. Prueba de Login
Después de aplicar los cambios, prueba el endpoint de login:

```bash
curl -X POST https://bienesinmuebles.imaarica.cl/auth/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "rut": "usuario_test",
    "password": "password_test"
  }'
```

## Configuración Adicional Recomendada

### 1. Configuración de Cookies Seguras
Para producción, asegúrate de que las cookies sean seguras:

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.imaarica.cl
```

### 2. Configuración de CORS
Verifica que CORS esté configurado correctamente para el dominio de producción:

```env
CORS_ALLOWED_ORIGINS=https://bienesinmuebles.imaarica.cl
```

### 3. Configuración de Logs
Habilita logs detallados para debugging:

```env
LOG_LEVEL=debug
APP_DEBUG=true  # Solo temporalmente para debugging
```

## Troubleshooting

### Si el problema persiste:

1. **Verificar logs de Laravel**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verificar logs del servidor web**:
   ```bash
   tail -f /var/log/apache2/error.log
   # o
   tail -f /var/log/nginx/error.log
   ```

3. **Verificar permisos de archivos**:
   ```bash
   chmod -R 755 storage/
   chown -R www-data:www-data storage/
   ```

4. **Verificar configuración de PHP**:
   ```bash
   php -m | grep session
   ```

### Comandos Útiles para Debugging

```bash
# Verificar configuración actual
php artisan config:show session
php artisan config:show sanctum

# Verificar rutas registradas
php artisan route:list --path=auth

# Verificar middleware aplicados
php artisan route:list --path=auth --verbose
```

## Notas Importantes

1. **Seguridad**: Después de solucionar el problema, asegúrate de deshabilitar `APP_DEBUG=true` en producción.

2. **Backup**: Siempre haz backup de la configuración antes de hacer cambios.

3. **Testing**: Prueba los cambios en un entorno de staging antes de aplicar en producción.

4. **Monitoreo**: Monitorea los logs después de aplicar los cambios para detectar cualquier problema.

## Contacto
Si el problema persiste después de aplicar estas soluciones, revisa:
- Logs de Laravel en `storage/logs/laravel.log`
- Logs del servidor web
- Configuración de PHP y extensiones
- Permisos de archivos y directorios 