# Configuración de Cookies para Laravel Sanctum

## 🔧 Variables de Entorno Requeridas

Agrega estas variables a tu archivo `.env`:

```env
# Configuración de Sesión
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.imaarica.cl
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_COOKIE=gestin_de_plan_de_compras_session

# Configuración de Sanctum
SANCTUM_STATEFUL_DOMAINS=dev.imaarica.cl,imaarica.cl

# Configuración de CORS
CORS_ALLOWED_ORIGINS=https://dev.imaarica.cl,https://imaarica.cl
CORS_SUPPORTS_CREDENTIALS=true

# Configuración de Cookies
COOKIE_DOMAIN=.imaarica.cl
COOKIE_SECURE=true
COOKIE_SAME_SITE=lax
```

## 📋 Explicación de las Variables

### SESSION_DOMAIN
- **Valor**: `.imaarica.cl`
- **Propósito**: Define el dominio base para las cookies de sesión
- **Importante**: El punto al inicio permite que la cookie sea válida para todos los subdominios

### SESSION_SECURE_COOKIE
- **Valor**: `true`
- **Propósito**: Asegura que las cookies solo se envíen por HTTPS
- **Requerido**: Para producción con SSL

### SESSION_SAME_SITE
- **Valor**: `lax`
- **Propósito**: Controla el comportamiento de las cookies en requests cross-site
- **Opciones**: `lax`, `strict`, `none`

### SANCTUM_STATEFUL_DOMAINS
- **Valor**: `dev.imaarica.cl,imaarica.cl`
- **Propósito**: Define qué dominios pueden recibir cookies de autenticación
- **Importante**: Debe incluir tu dominio Angular

## 🔍 Verificación de Configuración

### 1. Verificar Configuración Actual
```bash
php artisan config:cache
php artisan config:clear
php artisan route:clear
```

### 2. Verificar Cookies en Laravel
```php
// En una ruta de prueba
Route::get('/test-cookies', function () {
    return response()->json([
        'session_domain' => config('session.domain'),
        'session_secure' => config('session.secure'),
        'session_same_site' => config('session.same_site'),
        'sanctum_domains' => config('sanctum.stateful'),
        'cors_origins' => config('cors.allowed_origins'),
    ]);
});
```

### 3. Verificar en el Navegador
```javascript
// En la consola del navegador
console.log('Cookies actuales:', document.cookie);

// Verificar cookies específicas
console.log('XSRF-TOKEN:', document.cookie.includes('XSRF-TOKEN'));
console.log('Session:', document.cookie.includes('gestin_de_plan_de_compras_session'));
```

## 🚨 Problemas Comunes y Soluciones

### Problema: Cookies no se establecen
**Solución**: Verificar que `SESSION_DOMAIN` esté configurado correctamente

### Problema: Cookies duplicadas
**Solución**: El middleware `PreventDuplicateCookies` debería resolverlo

### Problema: Error CORS
**Solución**: Verificar que el dominio Angular esté en `allowed_origins`

### Problema: Cookies no persisten
**Solución**: Verificar `withCredentials: true` en Angular

## 📱 Configuración para Diferentes Entornos

### Desarrollo Local
```env
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SANCTUM_STATEFUL_DOMAINS=localhost:4200
```

### Producción
```env
SESSION_DOMAIN=.imaarica.cl
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=dev.imaarica.cl,imaarica.cl
```

## 🔧 Comandos de Limpieza

### Limpiar Cache de Configuración
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Limpiar Sesiones de Base de Datos
```bash
php artisan session:table
php artisan migrate
```

### Verificar Estado de Sesiones
```bash
php artisan tinker
>>> DB::table('sessions')->count();
>>> DB::table('sessions')->where('last_activity', '<', now()->subHours(1))->delete();
``` 