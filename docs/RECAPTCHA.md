# Implementación de reCAPTCHA v3 en Leykarin

## Descripción General

Este documento describe la implementación completa de Google reCAPTCHA v3 en el sistema Leykarin para proteger el formulario de denuncias contra spam y ataques automatizados.

## Características Implementadas

- ✅ Validación de reCAPTCHA v3 con score mínimo configurable
- ✅ Servicio dedicado `ReCaptchaService` para manejo centralizado
- ✅ Middleware opcional `VerifyReCaptcha`
- ✅ Regla de validación personalizada `ReCaptchaRule`
- ✅ Logging completo para auditoría de seguridad
- ✅ Configuración flexible via variables de entorno
- ✅ Comando de prueba `php artisan recaptcha:test`
- ✅ Manejo robusto de errores y timeouts
- ✅ Soporte para habilitación/deshabilitación

## Configuración

### 1. Variables de Entorno

Agregar al archivo `.env`:

```env
# Claves de reCAPTCHA (obtener de Google reCAPTCHA Admin Console)
RECAPTCHA_SITE_KEY=6Le48cwrAAAAABedSDcI682mOcNawqjCKT6BNGr9
RECAPTCHA_SECRET_KEY=tu-secret-key-aqui

# Configuración de validación
RECAPTCHA_ENABLED=true
RECAPTCHA_MIN_SCORE=0.5
RECAPTCHA_TIMEOUT=10
```

### 2. Configuración en config/services.php

```php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY', '6Le48cwrAAAAABedSDcI682mOcNawqjCKT6BNGr9'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
    'timeout' => env('RECAPTCHA_TIMEOUT', 10),
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
],
```

## Uso

### 1. En Controllers (Método Recomendado)

```php
use App\Services\ReCaptchaService;

class WebController extends Controller
{
    private ReCaptchaService $recaptchaService;

    public function __construct(ReCaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

    public function storeComplaint(ComplaintRequest $request)
    {
        $token = $request->input('recaptcha_token');
        
        // Validar reCAPTCHA
        $validationResponse = $this->recaptchaService->verifyOrFail(
            $token,
            $request->ip(),
            'submit_complaint'
        );

        if ($validationResponse !== null) {
            return $validationResponse; // Error response
        }

        // Continuar con el procesamiento...
    }
}
```

### 2. Como Middleware

Registrar en `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'recaptcha' => \App\Http\Middleware\VerifyReCaptcha::class,
];
```

Usar en rutas:

```php
Route::post('/complaints', [WebController::class, 'storeComplaint'])
    ->middleware('recaptcha:submit_complaint');
```

### 3. Como Regla de Validación

```php
use App\Rules\ReCaptchaRule;

public function rules()
{
    return [
        'recaptcha_token' => ['required', new ReCaptchaRule(request()->ip(), 'submit_complaint')],
        // otras reglas...
    ];
}
```

## Frontend Integration (Angular)

### 1. Instalación

```bash
npm install ng-recaptcha
```

### 2. Configuración en Angular

```typescript
// app.module.ts
import { RecaptchaV3Module, RECAPTCHA_V3_SITE_KEY } from 'ng-recaptcha';

@NgModule({
  imports: [
    RecaptchaV3Module,
    // ...
  ],
  providers: [
    {
      provide: RECAPTCHA_V3_SITE_KEY,
      useValue: '6Le48cwrAAAAABedSDcI682mOcNawqjCKT6BNGr9'
    },
    // ...
  ]
})
export class AppModule { }
```

### 3. Uso en Componente

```typescript
import { ReCaptchaV3Service } from 'ng-recaptcha';

export class ComplaintFormComponent {
  constructor(private recaptchaV3Service: ReCaptchaV3Service) {}

  async submitComplaint() {
    try {
      // Obtener token reCAPTCHA
      const token = await this.recaptchaV3Service.execute('submit_complaint').toPromise();
      
      // Agregar token al FormData
      const formData = new FormData();
      formData.append('recaptcha_token', token);
      // ... otros campos del formulario

      // Enviar al backend
      const response = await this.http.post('/api/v1/complaints', formData).toPromise();
      
    } catch (error) {
      console.error('Error al enviar denuncia:', error);
    }
  }
}
```

## Estructura de Respuestas

### Respuesta Exitosa

```json
{
  "success": true,
  "message": "Denuncia registrada exitosamente",
  "data": {
    "id": 123,
    "folio": "DEN-2024-001",
    // ... otros datos
  }
}
```

### Respuesta de Error reCAPTCHA

```json
{
  "success": false,
  "message": "Validación de seguridad fallida",
  "errors": {
    "recaptcha": ["Validación de seguridad fallida"]
  }
}
```

## Logging y Monitoreo

El sistema registra automáticamente:

- ✅ Validaciones exitosas con score
- ❌ Validaciones fallidas con razón
- ⚠️ Scores bajos (posible bot)
- 🚨 Errores de configuración
- 📊 Estadísticas de uso

### Logs Típicos

```
[INFO] Validación reCAPTCHA exitosa: score=0.9, action=submit_complaint
[WARNING] Score reCAPTCHA bajo: score=0.3, min_score=0.5
[ERROR] Validación reCAPTCHA fallida: error_codes=["invalid-input-response"]
```

## Comandos Útiles

### Probar Configuración

```bash
php artisan recaptcha:test
```

### Probar Token Específico

```bash
php artisan recaptcha:test "03AGdBq26..." --ip=192.168.1.1 --action=submit_complaint
```

## Seguridad y Mejores Prácticas

### 1. Configuración de Score

- **0.9-1.0**: Muy probablemente humano
- **0.7-0.8**: Probablemente humano
- **0.5-0.6**: Neutral (recomendado como mínimo)
- **0.1-0.4**: Probablemente bot
- **0.0**: Muy probablemente bot

### 2. Acciones Recomendadas

- `submit_complaint`: Para formulario de denuncias
- `login`: Para inicio de sesión
- `register`: Para registro de usuarios
- `contact`: Para formularios de contacto

### 3. Monitoreo

- Revisar logs regularmente para detectar patrones
- Ajustar score mínimo según necesidades
- Implementar alertas para intentos masivos

## Troubleshooting

### Error: "RECAPTCHA_SECRET_KEY no configurada"

**Solución**: Verificar que `RECAPTCHA_SECRET_KEY` esté en el archivo `.env`

### Error: "Token reCAPTCHA expirado"

**Solución**: Los tokens tienen validez de 2 minutos. Generar nuevo token antes de enviar.

### Score siempre bajo

**Posibles causas**:
- Dominio no registrado en Google reCAPTCHA
- Site Key incorrecta
- Acción no coincide

### Timeout en validación

**Solución**: Aumentar `RECAPTCHA_TIMEOUT` en `.env`

## Configuración de Producción

### 1. Google reCAPTCHA Console

1. Ir a https://www.google.com/recaptcha/admin
2. Agregar dominio: `leykarin.imaarica.cl`
3. Configurar tipo: reCAPTCHA v3
4. Obtener Site Key y Secret Key

### 2. Variables de Producción

```env
RECAPTCHA_SITE_KEY=tu-site-key-de-produccion
RECAPTCHA_SECRET_KEY=tu-secret-key-de-produccion
RECAPTCHA_ENABLED=true
RECAPTCHA_MIN_SCORE=0.5
```

## Mantenimiento

### Logs a Revisar

- `storage/logs/laravel.log`: Logs generales
- Buscar: `recaptcha_success`, `recaptcha_failed`, `recaptcha_low_score`

### Métricas Importantes

- Tasa de éxito de validaciones
- Distribución de scores
- Intentos con score bajo
- Errores de configuración

## Soporte

Para problemas o dudas:

1. Revisar logs en `storage/logs/laravel.log`
2. Ejecutar `php artisan recaptcha:test`
3. Verificar configuración en Google reCAPTCHA Console
4. Consultar documentación oficial: https://developers.google.com/recaptcha/docs/v3
