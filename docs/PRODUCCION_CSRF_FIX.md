# Solución de Error CSRF en Producción

## 🔴 Problema Identificado

Error en producción:
```json
{
  "status": 500,
  "error": {
    "message": "CSRF token mismatch."
  }
}
```

## 🔍 Causa Raíz

Las rutas de autenticación en `routes/api.php` tenían el middleware `web` que incluye verificación CSRF, pero Angular (SPA) no estaba enviando tokens CSRF porque:

1. **Arquitectura SPA**: Angular hace llamadas API puras sin cookies de sesión
2. **Middleware web**: Incluye `VerifyCsrfToken` que requiere token CSRF
3. **Sin flujo CSRF**: Angular no obtiene ni envía el token CSRF de Laravel

## ✅ Solución Implementada

### 1. **Removido middleware `web` de rutas de autenticación**

**Antes:**
```php
Route::prefix('auth')->middleware(['web'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // ...
});
```

**Después:**
```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // ...
});
```

### 2. **Agregadas excepciones CSRF globales**

En `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'telescope/*',
    'api/v1/auth/*',              // ← Todas las rutas de autenticación
    'api/v1/web/complaints',       // ← Formulario público
    'api/v1/temporary-files',      // ← Carga de archivos
    'api/v1/temporary-files/*',
];
```

## 📋 Configuración de Producción

### **Variables de Entorno (.env en producción)**

```env
# Aplicación
APP_ENV=production
APP_DEBUG=false
APP_URL=https://leykarin2.imaarica.cl

# Frontend
FRONTEND_URL=https://leykarin2.imaarica.cl

# CORS
CORS_ALLOWED_ORIGINS=https://leykarin2.imaarica.cl

# Sanctum
SANCTUM_STATEFUL_DOMAINS=leykarin2.imaarica.cl

# Sesión (para HTTPS)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_DOMAIN=.imaarica.cl

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leykarin_production
DB_USERNAME=leykarin_user
DB_PASSWORD=your_secure_password
```

## 🚀 Pasos para Desplegar

### 1. **Actualizar archivos en servidor**
```bash
# Subir cambios al servidor
git pull origin main
# o copiar archivos modificados
```

### 2. **Actualizar .env en producción**
```bash
# Editar .env con las variables correctas
nano .env
```

### 3. **Limpiar y optimizar cache**
```bash
# Limpiar cache existente
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. **Verificar permisos**
```bash
# Asegurar permisos correctos
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. **Reiniciar servicios**
```bash
# Reiniciar PHP-FPM
sudo systemctl restart php8.0-fpm

# Reiniciar Nginx/Apache
sudo systemctl restart nginx
# o
sudo systemctl restart apache2
```

## 🧪 Verificación

### **1. Probar endpoint de login**
```bash
curl -X POST https://leykarin2.imaarica.cl/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "rut": "12345678-9",
    "password": "password123"
  }'
```

### **2. Probar formulario web**
```bash
curl -X POST https://leykarin2.imaarica.cl/api/v1/web/complaints \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "complainant": {...},
    "denounced": {...}
  }'
```

### **3. Verificar CORS**
```bash
curl -H "Origin: https://leykarin2.imaarica.cl" \
     -H "Access-Control-Request-Method: POST" \
     -X OPTIONS https://leykarin2.imaarica.cl/api/v1/auth/login -v
```

## 🔒 Seguridad Mantenida

Aunque removimos CSRF de rutas API, la seguridad se mantiene porque:

### **Para Autenticación:**
- ✅ **Sanctum Tokens**: Autenticación basada en tokens API
- ✅ **Rate Limiting**: Límites de intentos de login
- ✅ **2FA**: Autenticación de dos factores por email
- ✅ **Logging**: Registro completo de intentos de autenticación

### **Para Google OAuth:**
- ✅ **ID Token Verification**: Verificación completa con Google API
- ✅ **Domain Validation**: Solo dominios corporativos autorizados
- ✅ **Pre-registered Users**: Solo usuarios existentes pueden acceder

### **Para Formularios Web:**
- ✅ **reCAPTCHA v3**: Protección contra bots y spam
- ✅ **Rate Limiting**: Límites de envíos por IP
- ✅ **Validation**: Validación exhaustiva de datos

## 📊 Monitoreo

### **Logs a revisar:**
```bash
# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs de Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Logs de PHP
tail -f /var/log/php8.0-fpm.log
```

### **Métricas importantes:**
- Intentos de login fallidos
- Errores 500 en endpoints de autenticación
- Errores CORS
- Tiempos de respuesta de API

## ⚠️ Notas Importantes

1. **CSRF solo para rutas web tradicionales**: Si tienes rutas que usan sesiones web tradicionales (no API), esas SÍ necesitan CSRF.

2. **API Routes vs Web Routes**: 
   - `routes/api.php` → Sin CSRF (para SPAs)
   - `routes/web.php` → Con CSRF (para vistas Blade tradicionales)

3. **Sanctum Stateful**: Si usas Sanctum con cookies (stateful), asegúrate de que el dominio frontend esté en `SANCTUM_STATEFUL_DOMAINS`.

4. **HTTPS en producción**: Siempre usa HTTPS en producción para cookies seguras.

## 🆘 Troubleshooting

### **Si aún hay errores CSRF:**

1. **Verificar que cache esté limpio:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   ```

2. **Verificar que .env esté correcto:**
   ```bash
   php artisan config:show cors
   php artisan config:show session
   ```

3. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep CSRF
   ```

4. **Verificar headers en navegador:**
   - Abrir DevTools → Network
   - Verificar que `Origin` header coincida con `CORS_ALLOWED_ORIGINS`
   - Verificar que no haya errores CORS

### **Si hay errores CORS:**

1. **Verificar dominio en .env:**
   ```env
   CORS_ALLOWED_ORIGINS=https://leykarin2.imaarica.cl
   ```

2. **Verificar que no haya espacios o caracteres extra**

3. **Reiniciar servidor después de cambios**

## 📞 Contacto

Si el problema persiste después de seguir estos pasos:
1. Revisar logs detallados
2. Verificar configuración de servidor web (Nginx/Apache)
3. Confirmar que SSL/HTTPS esté funcionando correctamente
4. Verificar que el frontend esté haciendo las llamadas a las URLs correctas

---

**Última actualización:** 2025-09-29
**Estado:** ✅ Solución implementada y probada
