<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SecurityLogService
{
    /**
     * Log de intentos de login exitosos
     */
    public static function logSuccessfulLogin($user, Request $request): void
    {
        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_rut' => $user->rut,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ];

        // Usar canal de log configurado en variables de entorno
        $channel = env('SECURITY_LOG_CHANNEL', 'security');
        $level = env('SECURITY_LOG_LEVEL', 'info');
        
        Log::channel($channel)->log($level, 'Login successful', $logData);
        
        // Enviar alerta si está habilitado
        if (env('SECURITY_MONITORING_ENABLED', false)) {
            self::sendSecurityAlert('Login successful', $logData);
        }
    }

    /**
     * Log de intentos de login fallidos
     */
    public static function logFailedLogin($credentials, Request $request): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'attempted_rut' => $credentials['rut'] ?? 'unknown',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de login con cuenta suspendida
     */
    public static function logSuspendedAccountLogin($user, Request $request): void
    {
        Log::channel('security')->warning('Suspended account login attempt', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_rut' => $user->rut,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de logout
     */
    public static function logLogout($user, Request $request): void
    {
        Log::channel('security')->info('User logout', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_rut' => $user->rut,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de reset de contraseña
     */
    public static function logPasswordResetAttempt($email, Request $request): void
    {
        Log::channel('security')->info('Password reset attempt', [
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de reset de contraseña exitoso
     */
    public static function logPasswordResetSuccess($user, Request $request): void
    {
        Log::channel('security')->info('Password reset successful', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_rut' => $user->rut,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de acceso no autorizado
     */
    public static function logUnauthorizedAccess($route, Request $request): void
    {
        Log::channel('security')->warning('Unauthorized access attempt', [
            'route' => $route,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de acceso con roles insuficientes
     */
    public static function logInsufficientPermissions($user, $requiredPermission, Request $request): void
    {
        Log::channel('security')->warning('Insufficient permissions', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_rut' => $user->rut,
            'required_permission' => $requiredPermission,
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'route' => $request->route()->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de rate limiting
     */
    public static function logRateLimitExceeded($route, Request $request): void
    {
        Log::channel('security')->warning('Rate limit exceeded', [
            'route' => $route,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de inyección SQL
     */
    public static function logSqlInjectionAttempt($input, Request $request): void
    {
        Log::channel('security')->critical('SQL injection attempt detected', [
            'input' => $input,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de intentos de XSS
     */
    public static function logXssAttempt($input, Request $request): void
    {
        Log::channel('security')->critical('XSS attempt detected', [
            'input' => $input,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de archivos sospechosos
     */
    public static function logSuspiciousFileUpload($file, Request $request): void
    {
        Log::channel('security')->warning('Suspicious file upload attempt', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'route' => $request->route()->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Log de cambios de roles/permisos
     */
    public static function logRolePermissionChange($adminUser, $targetUser, $action, $details): void
    {
        $logData = [
            'admin_user_id' => $adminUser->id,
            'admin_user_email' => $adminUser->email,
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];

        $channel = env('SECURITY_LOG_CHANNEL', 'security');
        $level = env('SECURITY_LOG_LEVEL', 'info');
        
        Log::channel($channel)->log($level, 'Role/Permission change', $logData);
    }

    /**
     * Enviar alerta de seguridad por email
     */
    protected static function sendSecurityAlert(string $event, array $data): void
    {
        $alertEmail = env('SECURITY_ALERT_EMAIL');
        
        if (!$alertEmail) {
            return;
        }

        try {
            // Aquí se implementaría el envío de email de alerta
            // Por ahora solo se registra en el log
            Log::channel('security')->alert('Security alert sent', [
                'event' => $event,
                'alert_email' => $alertEmail,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::channel('security')->error('Failed to send security alert', [
                'error' => $e->getMessage(),
                'event' => $event
            ]);
        }
    }
} 