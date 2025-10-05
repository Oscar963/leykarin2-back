# Reporte de Auditoría de Seguridad - Sistema Leykarin2 Backend

**Fecha:** 30 de Septiembre, 2025  
**Versión:** Laravel 8.x  
**Auditor:** Cascade AI  
**Calificación General:** 7.5/10

---

## 📊 RESUMEN EJECUTIVO

La aplicación presenta una base sólida de seguridad con implementaciones robustas de autenticación (OAuth, 2FA), logging de seguridad y uso de tokens seguros. Sin embargo, se identificaron **4 hallazgos críticos** relacionados principalmente con autorización granular que deben corregirse antes de desplegar a producción.

### Estadísticas
- **Hallazgos Críticos:** 4
- **Hallazgos Importantes:** 6
- **Mejoras Recomendadas:** 8
- **Aspectos Positivos:** 10

---

## 🔴 HALLAZGOS CRÍTICOS

### 1. Falta de Autorización en Rutas Protegidas
**Severidad:** CRÍTICA  
**Ubicación:** `routes/api.php` líneas 88-113  
**CWE:** CWE-862 (Missing Authorization)

**Descripción:**  
Las rutas de recursos críticos (denuncias, usuarios, roles, permisos) no implementan middleware de verificación de permisos de Spatie.

**Código Vulnerable:**
```php
Route::apiResource('complaints', ComplaintController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);
```

**Impacto:**  
Cualquier usuario autenticado puede:
- Ver todas las denuncias del sistema
- Crear, editar y eliminar usuarios
- Modificar roles y permisos
- Acceder a información confidencial

**Solución Recomendada:**
```php
Route::apiResource('complaints', ComplaintController::class)
    ->middleware('permission:complaints.list|complaints.create|complaints.edit|complaints.delete');

Route::apiResource('users', UserController::class)
    ->middleware('permission:users.manage');

Route::apiResource('roles', RoleController::class)
    ->middleware('permission:roles.manage');

Route::apiResource('permissions', PermissionController::class)
    ->middleware('permission:permissions.manage');
```

**Prioridad:** INMEDIATA

---

### 2. Descarga de PDF Sin Validación de Permisos
**Severidad:** CRÍTICA  
**Ubicación:** `app/Http/Controllers/ComplaintController.php` línea 155  
**CWE:** CWE-639 (Authorization Bypass Through User-Controlled Key)

**Descripción:**  
El endpoint de descarga de PDF valida únicamente el token pero no verifica si el usuario tiene permisos para descargar o si es el propietario de la denuncia.

**Código Vulnerable:**
```php
public function downloadPdf(string $token)
{
    $complaint = $this->complaintService->getComplaintByTokenForDownload($token);
    // No valida permisos ni propiedad
    return response($pdf->output(), 200, [...]);
}
```

**Impacto:**  
Un usuario autenticado con un token válido (obtenido por ingeniería social, phishing, etc.) puede descargar PDFs de denuncias confidenciales que no le corresponden.

**Solución Recomendada:**
```php
public function downloadPdf(string $token)
{
    $complaint = $this->complaintService->getComplaintByTokenForDownload($token);
    
    if (!$complaint) {
        abort(404, 'Denuncia no encontrada');
    }
    
    // Validar permisos o propiedad
    $user = auth()->user();
    $isOwner = $complaint->complainant->email === $user->email;
    $hasPermission = $user->can('complaints.download');
    
    if (!$isOwner && !$hasPermission) {
        abort(403, 'No autorizado para descargar esta denuncia');
    }
    
    // Continuar con la descarga...
}
```

**Prioridad:** INMEDIATA

---

### 3. Endpoint Público Sin Rate Limiting Específico
**Severidad:** CRÍTICA  
**Ubicación:** `routes/api.php` línea 24  
**CWE:** CWE-770 (Allocation of Resources Without Limits)

**Descripción:**  
El endpoint público de creación de denuncias no tiene throttling específico, solo el genérico de API.

**Código Vulnerable:**
```php
Route::post('web/complaints', [WebController::class, 'storeComplaint'])
    ->name('web.complaints.store');
```

**Impacto:**  
- Ataques de spam con denuncias falsas
- Flooding de la base de datos
- Saturación del sistema de emails
- Abuso del servicio reCAPTCHA

**Solución Recomendada:**
```php
Route::post('web/complaints', [WebController::class, 'storeComplaint'])
    ->middleware('throttle:5,60') // 5 denuncias por hora por IP
    ->name('web.complaints.store');
```

**Configuración Adicional en `.env`:**
```env
RATE_LIMIT_COMPLAINT_SUBMISSION=5,60
```

**Prioridad:** INMEDIATA

---

### 4. Falta de Sanitización en Campos de Texto Largo
**Severidad:** CRÍTICA  
**Ubicación:** `app/Http/Requests/ComplaintRequest.php` líneas 70-71  
**CWE:** CWE-79 (Cross-site Scripting)

**Descripción:**  
Los campos de narrativas no tienen límite de longitud ni sanitización contra XSS.

**Código Vulnerable:**
```php
'circumstances_narrative' => 'required|string',
'consequences_narrative' => 'required|string',
```

**Impacto:**  
- Inyección de scripts maliciosos en narrativas
- XSS almacenado que afecta a administradores
- Posible exfiltración de datos de sesión

**Solución Recomendada:**
```php
'circumstances_narrative' => 'required|string|max:5000',
'consequences_narrative' => 'required|string|max:5000',
```

**Agregar en el modelo o servicio:**
```php
protected function sanitizeNarrative(string $text): string
{
    return strip_tags($text, '<p><br><strong><em><ul><ol><li>');
}
```

**Prioridad:** INMEDIATA

---

## 🟠 HALLAZGOS IMPORTANTES

### 5. Exposición de Información Sensible en Logs
**Severidad:** ALTA  
**Ubicación:** `app/Http/Controllers/ComplaintController.php` línea 195

**Descripción:**  
Los logs incluyen datos validados completos que pueden contener información sensible (emails, RUTs, direcciones).

**Solución:**
```php
private function handleException(Throwable $e, string $userMessage, array $context = []): JsonResponse
{
    // Filtrar datos sensibles
    $safeContext = array_diff_key($context, array_flip([
        'complainant_email', 'complainant_rut', 'complainant_address',
        'denounced_email', 'denounced_rut', 'password'
    ]));
    
    Log::error($e->getMessage(), array_merge($safeContext, [
        'user_id' => auth()->id(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]));
    // ...
}
```

---

### 6. Falta de Validación de Propiedad en Reenvío de Comprobante
**Severidad:** ALTA  
**Ubicación:** `app/Http/Controllers/ComplaintController.php` línea 124

**Descripción:**  
Cualquier usuario autenticado puede reenviar comprobantes de cualquier denuncia si conoce el token.

**Solución:**
```php
public function resendReceipt(Request $request): JsonResponse
{
    $validated = $request->validate([
        'email' => 'required|string|email',
        'token' => 'required|string|min:10'
    ]);

    $complaint = $this->complaintService->reenviarComprobante(
        $validated['email'], 
        $validated['token']
    );
    
    // Validar propiedad o permisos
    $user = auth()->user();
    if ($complaint->complainant->email !== $validated['email'] && 
        !$user->can('complaints.resend')) {
        abort(403, 'No autorizado');
    }
    
    // Continuar...
}
```

---

### 7. Uso de DB::table en Lugar de Modelos Eloquent
**Severidad:** MEDIA  
**Ubicación:** `app/Services/ComplaintService.php` líneas 177-199

**Descripción:**  
Uso directo de Query Builder sin modelo Eloquent para `complaint_counters`.

**Solución:**  
Crear modelo `ComplaintCounter`:
```php
php artisan make:model ComplaintCounter
```

```php
class ComplaintCounter extends Model
{
    protected $fillable = ['type_dependency_id', 'year', 'current_seq'];
    
    public function typeDependency()
    {
        return $this->belongsTo(TypeDependency::class);
    }
}
```

---

### 8. Falta de Validación de Límite en Archivos Temporales
**Severidad:** MEDIA  
**Ubicación:** `app/Services/FileService.php`

**Descripción:**  
No hay límite de archivos o tamaño total por sesión temporal.

**Solución:**
```php
public function uploadTemporaryFile(string $sessionId, UploadedFile $uploadedFile, 
                                   string $fileType, string $disk = 'public'): TemporaryFile
{
    // Validar límites por sesión
    $existingFiles = $this->getTemporaryFiles($sessionId);
    
    if ($existingFiles->count() >= 10) {
        throw ValidationException::withMessages([
            'file' => 'Máximo 10 archivos por sesión'
        ]);
    }
    
    $totalSize = $existingFiles->sum('size');
    if (($totalSize + $uploadedFile->getSize()) > 52428800) { // 50MB
        throw ValidationException::withMessages([
            'file' => 'Tamaño total excede 50MB'
        ]);
    }
    
    // Continuar...
}
```

---

### 9. CSP Demasiado Permisivo en Producción
**Severidad:** MEDIA  
**Ubicación:** `app/Http/Middleware/SecurityHeaders.php` línea 42

**Descripción:**  
Content Security Policy permite `unsafe-inline` y `unsafe-eval` en producción.

**Solución:**
```php
if (app()->environment('production')) {
    $csp = [
        "default-src 'self'",
        "script-src 'self'", // Eliminar unsafe-inline y unsafe-eval
        "style-src 'self' fonts.googleapis.com",
        "font-src 'self' fonts.gstatic.com",
        "img-src 'self' data: https:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'"
    ];
    $response->headers->set('Content-Security-Policy', implode('; ', $csp));
}
```

---

### 10. Falta de Validación Estricta de Dominio en Google OAuth
**Severidad:** MEDIA  
**Ubicación:** Configuración general

**Descripción:**  
Aunque existe validación de dominio, no está forzada en todas las rutas OAuth.

**Solución:**  
Asegurar en `.env` de producción:
```env
GOOGLE_ALLOWED_DOMAIN=municipalidadarica.cl
GOOGLE_OAUTH_AUTO_REGISTER=false
```

---

## 🟡 MEJORAS RECOMENDADAS

### 11. Falta de Índices en Búsquedas Frecuentes
**Ubicación:** Migraciones de base de datos

**Solución:**
```php
// En migración de complaints
$table->index('folio');
$table->index('token');
$table->index('created_at');

// En migración de users
$table->index('rut');
$table->index('email');
```

---

### 12. Falta de Soft Deletes en Archivos
**Ubicación:** `app/Models/File.php`

**Solución:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;
    // ...
}
```

---

### 13. Falta de Validación de RUT Chileno
**Ubicación:** `app/Http/Requests/ComplaintRequest.php`

**Solución:**  
Crear regla personalizada:
```php
php artisan make:rule ValidRut
```

```php
public function rules(): array
{
    return [
        'complainant_rut' => ['required', 'string', new ValidRut],
        'denounced_rut' => ['nullable', 'string', new ValidRut],
    ];
}
```

---

### 14. Falta de Paginación en getAllFiles()
**Ubicación:** `app/Services/FileService.php` línea 26

**Solución:**
```php
public function getAllFiles(int $perPage = 15): LengthAwarePaginator
{
    return File::latest()->paginate($perPage);
}
```

---

### 15. Falta de Validación de Email en Reenvío
**Ubicación:** `app/Services/ComplaintService.php` línea 263

**Solución:**
```php
public function reenviarComprobante(string $email, string $token): Complaint
{
    $complaint = $this->getComplaintByTokenForDownload($token);
    
    // Validar que el email pertenece al denunciante original
    if ($complaint->complainant->email !== $email) {
        throw new \Exception('El email no coincide con el denunciante original.');
    }
    
    // Continuar...
}
```

---

### 16. Falta de Límite en Cantidad de Testigos
**Ubicación:** `app/Http/Requests/ComplaintRequest.php`

**Solución:**
```php
'witnesses' => 'nullable|array|max:10',
```

---

### 17. Falta de Logging en Cambios de Roles
**Ubicación:** Sistema de roles

**Solución:**  
Crear observer para modelo User:
```php
php artisan make:observer UserObserver --model=User
```

```php
public function updated(User $user)
{
    if ($user->isDirty('roles')) {
        Log::channel('security')->info('Roles modificados', [
            'user_id' => $user->id,
            'old_roles' => $user->getOriginal('roles'),
            'new_roles' => $user->roles,
            'modified_by' => auth()->id()
        ]);
    }
}
```

---

### 18. Configuración de CORS Muy Permisiva
**Ubicación:** `config/cors.php`

**Solución:**
```php
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
```

---

## ✅ ASPECTOS POSITIVOS

1. ✅ **Excelente implementación de Google OAuth** con validación completa de ID tokens
2. ✅ **Sistema de 2FA por email** correctamente implementado con códigos temporales
3. ✅ **Middleware de seguridad robusto** con headers HTTP apropiados
4. ✅ **Uso de tokens criptográficamente seguros** para descarga de PDFs
5. ✅ **Logging completo de actividades** con SecurityLogService
6. ✅ **Validación de reCAPTCHA v3** en formularios públicos
7. ✅ **Filtrado por dependencia organizacional** correctamente implementado
8. ✅ **Uso de transacciones de base de datos** en operaciones críticas
9. ✅ **Soft deletes en modelos principales** (User, Complaint)
10. ✅ **Validaciones robustas** mediante FormRequests

---

## 📋 PLAN DE ACCIÓN PRIORIZADO

### 🔴 Acción Inmediata (Antes de Producción)
- [ ] Agregar middleware de permisos a todas las rutas protegidas
- [ ] Implementar validación de permisos en descarga de PDFs
- [ ] Agregar rate limiting específico a endpoint público de denuncias
- [ ] Sanitizar y limitar longitud de campos de texto largo

**Tiempo estimado:** 4-6 horas

---

### 🟠 Corto Plazo (1-2 semanas)
- [ ] Validar propiedad en reenvío de comprobantes
- [ ] Crear modelo ComplaintCounter y refactorizar
- [ ] Mejorar CSP eliminando unsafe-inline/unsafe-eval
- [ ] Implementar validación de RUT chileno
- [ ] Agregar límites a archivos temporales
- [ ] Filtrar datos sensibles en logs

**Tiempo estimado:** 2-3 días

---

### 🟡 Mediano Plazo (1 mes)
- [ ] Agregar índices de base de datos para optimización
- [ ] Implementar soft deletes en modelo File
- [ ] Limitar cantidad de testigos por denuncia
- [ ] Mejorar configuración de CORS
- [ ] Implementar logging de cambios de roles
- [ ] Agregar paginación en getAllFiles()

**Tiempo estimado:** 1-2 días

---

## 🛠️ COMANDOS ÚTILES

```bash
# Verificar rutas y middlewares
php artisan route:list --columns=uri,method,middleware,name

# Listar permisos del sistema
php artisan permission:show

# Limpiar archivos temporales expirados
php artisan schedule:run

# Verificar configuración de seguridad
php artisan config:show cors
php artisan config:show services.google

# Ejecutar tests
php artisan test --filter Security

# Generar documentación API actualizada
php artisan l5-swagger:generate

# Optimizar para producción
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📊 MÉTRICAS DE SEGURIDAD

| Categoría | Puntuación | Observaciones |
|-----------|------------|---------------|
| Autenticación | 9/10 | Excelente con OAuth y 2FA |
| Autorización | 5/10 | **Crítico:** Falta middleware de permisos |
| Validación de Entrada | 7/10 | Buena pero falta sanitización |
| Manejo de Sesiones | 8/10 | Correcto con Sanctum |
| Logging y Auditoría | 8/10 | Completo pero expone datos sensibles |
| Configuración | 7/10 | Buena base, mejorar CSP y CORS |
| Gestión de Archivos | 7/10 | Sólida pero sin límites |
| Protección XSS/CSRF | 7/10 | Headers correctos, mejorar sanitización |

**Puntuación Global: 7.5/10**

---

## 📝 NOTAS FINALES

Esta aplicación demuestra un buen nivel de madurez en seguridad con implementaciones avanzadas como Google OAuth, 2FA y logging robusto. Los hallazgos críticos identificados son principalmente de **autorización granular** y pueden corregirse rápidamente agregando los middlewares apropiados.

**Recomendación:** No desplegar a producción hasta corregir los 4 hallazgos críticos. Los demás pueden abordarse de forma incremental.

---

**Próxima Auditoría Recomendada:** 3 meses después del despliegue a producción

**Contacto para Consultas:** [Agregar información de contacto]

---

*Documento generado automáticamente por Cascade AI - Auditoría de Seguridad*
