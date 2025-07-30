# Implementación de Clave Única - Guía Completa

## Descripción General

Esta implementación permite la autenticación de usuarios mediante Clave Única, el sistema de identidad digital del Estado de Chile. Los usuarios pueden iniciar sesión usando sus credenciales gubernamentales sin necesidad de crear una cuenta separada.

## Arquitectura de la Implementación

### Componentes Principales

1. **SocialiteProviders\ClaveUnica**: Provider OAuth2 para Laravel Socialite
2. **AuthController**: Maneja el flujo de autenticación
3. **RutHelper**: Utilidades para validación y normalización de RUT
4. **SecurityLogService**: Registro de eventos de seguridad

### Flujo de Autenticación

```
Usuario → Frontend → /auth/claveunica/redirect → Clave Única → /auth/claveunica/callback → Frontend
```

## Configuración

### 1. Variables de Entorno

Agregar al archivo `.env`:

```env
# Configuración de Clave Única
CLAVEUNICA_CLIENT_ID=tu_client_id
CLAVEUNICA_CLIENT_SECRET=tu_client_secret
CLAVEUNICA_REDIRECT_URI=http://localhost:8000/api/v1/auth/claveunica/callback

# Frontend URL para redirecciones
FRONTEND_URL=http://localhost:3000
```

### 2. Obtener Credenciales

Para obtener las credenciales de Clave Única:

1. **Ambiente de Desarrollo/Testing:**
   - URL: https://www.claveunica.gob.cl/institucional/
   - Solicitar acceso al ambiente de integración
   - Proporcionar datos de la institución y aplicación

2. **Ambiente de Producción:**
   - Completar proceso de certificación
   - Cumplir requisitos de seguridad
   - Obtener aprobación institucional

### 3. URLs de Callback

Configurar en Clave Única las siguientes URLs:

- **Desarrollo:** `http://localhost:8000/api/v1/auth/claveunica/callback`
- **Producción:** `https://tu-dominio.cl/api/v1/auth/claveunica/callback`

## Implementación Técnica

### Rutas Configuradas

```php
// Rutas públicas (no requieren autenticación)
Route::get('/auth/claveunica/redirect', [AuthController::class, 'redirectToClaveUnica']);
Route::get('/auth/claveunica/callback', [AuthController::class, 'handleClaveUnicaCallback']);
```

### Datos del Usuario

Clave Única retorna la siguiente estructura de datos:

```json
{
  "id": "12345678K",
  "email": "usuario@email.com",
  "user": {
    "name": {
      "nombres": ["JUAN", "CARLOS"],
      "apellidos": ["PÉREZ", "GONZÁLEZ"]
    }
  }
}
```

### Validación de RUT

La clase `RutHelper` proporciona:

- `validate($rut)`: Valida formato y dígito verificador
- `normalize($rut)`: Normaliza para almacenamiento
- `format($rut)`: Formatea con puntos y guión
- `clean($rut)`: Remueve formato

## Manejo de Errores

### Códigos de Error

| Código | Descripción | Acción |
|--------|-------------|---------|
| `invalid_rut` | RUT inválido o vacío | Mostrar error de validación |
| `invalid_state` | Estado OAuth inválido | Reintentar autenticación |
| `account_suspended` | Cuenta deshabilitada | Contactar administrador |
| `claveunica_failed` | Error general | Reintentar o usar login tradicional |

### Logging

Todos los eventos se registran en logs:

```php
Log::info('Clave Única Response', $userData);
Log::error('Clave Única: RUT inválido', ['rut' => $rawRut]);
```

## Seguridad

### Medidas Implementadas

1. **Validación de RUT**: Verificación de formato y dígito verificador
2. **Normalización de datos**: Limpieza y estandarización
3. **Logging de seguridad**: Registro de todos los eventos
4. **Verificación de estado**: Control de cuentas activas/suspendidas
5. **Regeneración de sesión**: Prevención de session fixation

### Consideraciones de Seguridad

- **HTTPS obligatorio** en producción
- **Validación de estado OAuth** para prevenir CSRF
- **Timeout de sesión** configurado adecuadamente
- **Logs de auditoría** para todos los accesos

## Testing

### Ambiente de Pruebas

Clave Única proporciona un ambiente de testing con usuarios ficticios:

- RUT de prueba: `11111111-1`
- Contraseña: `clave123`

### Tests Automatizados

```php
// Ejemplo de test
public function test_clave_unica_callback_success()
{
    // Mock de respuesta de Clave Única
    $mockUser = new \Laravel\Socialite\Two\User();
    $mockUser->id = '12345678K';
    $mockUser->email = 'test@test.cl';
    
    // Simular callback
    $response = $this->get('/api/v1/auth/claveunica/callback');
    
    // Verificar redirección exitosa
    $this->assertRedirect(config('app.frontend_url') . '/dashboard?login=success');
}
```

## Monitoreo y Métricas

### Métricas Importantes

1. **Tasa de éxito de autenticación**
2. **Tiempo de respuesta de Clave Única**
3. **Errores por tipo**
4. **Usuarios únicos por día/mes**

### Alertas Recomendadas

- Tasa de error > 5%
- Tiempo de respuesta > 10 segundos
- Múltiples intentos fallidos del mismo usuario

## Troubleshooting

### Problemas Comunes

1. **Error "invalid_state"**
   - Verificar configuración de sesiones
   - Comprobar que las cookies estén habilitadas

2. **RUT inválido**
   - Verificar formato de respuesta de Clave Única
   - Comprobar logs para ver datos recibidos

3. **Redirección incorrecta**
   - Verificar `FRONTEND_URL` en `.env`
   - Comprobar configuración de CORS

### Comandos Útiles

```bash
# Limpiar cache de configuración
php artisan config:clear

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Verificar configuración de Socialite
php artisan tinker
>>> config('services.claveunica')
```

## Cumplimiento Normativo

### Requisitos de Clave Única

1. **Política de Privacidad**: Declarar uso de datos personales
2. **Términos de Uso**: Incluir términos específicos de Clave Única
3. **Logo Oficial**: Usar botones y logos oficiales
4. **Certificación SSL**: Obligatorio en producción

### Documentación Oficial

- [Manual Técnico de Clave Única](https://www.claveunica.gob.cl/institucional/)
- [Guía de Integración OAuth2](https://developers.claveunica.gob.cl/)
- [Términos y Condiciones](https://claveunica.gob.cl/terminos/)

## Contacto y Soporte

Para soporte técnico de Clave Única:
- Email: soporte@claveunica.gob.cl
- Teléfono: +56 2 2486 4000
- Portal de desarrolladores: https://developers.claveunica.gob.cl/
