<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\SecurityAlertService;

/**
 * Comando para ejecutar auditoría de seguridad completa.
 * 
 * Verifica dependencias, configuración, permisos y SSL.
 */
class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit {--send-alerts : Enviar alertas si se encuentran vulnerabilidades}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta auditoría de seguridad completa';

    /**
     * Security alert service instance
     *
     * @var SecurityAlertService
     */
    protected $alertService;

    /**
     * Create a new command instance.
     *
     * @param SecurityAlertService $alertService
     */
    public function __construct(SecurityAlertService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Iniciando auditoría de seguridad...');
        $this->newLine();
        
        $vulnerabilities = [];

        // 1. Auditar dependencias de Composer
        $this->info('📦 Auditando dependencias de Composer...');
        $composerVulns = $this->auditComposerDependencies();
        if (!empty($composerVulns)) {
            $vulnerabilities['composer'] = $composerVulns;
            $this->error('❌ Vulnerabilidades encontradas en Composer');
        } else {
            $this->info('✅ Sin vulnerabilidades en Composer');
        }

        // 2. Verificar configuración de seguridad
        $this->info('⚙️  Verificando configuración de seguridad...');
        $configIssues = $this->checkSecurityConfig();
        if (!empty($configIssues)) {
            $vulnerabilities['config'] = $configIssues;
            $this->error('❌ Problemas de configuración encontrados:');
            foreach ($configIssues as $issue) {
                $this->line('   • ' . $issue);
            }
        } else {
            $this->info('✅ Configuración de seguridad correcta');
        }

        // 3. Verificar permisos de archivos
        $this->info('🔒 Verificando permisos de archivos...');
        $permissionIssues = $this->checkFilePermissions();
        if (!empty($permissionIssues)) {
            $vulnerabilities['permissions'] = $permissionIssues;
            $this->error('❌ Problemas de permisos encontrados:');
            foreach ($permissionIssues as $issue) {
                $this->line('   • ' . $issue);
            }
        } else {
            $this->info('✅ Permisos de archivos correctos');
        }

        // 4. Verificar SSL/TLS (solo en producción)
        if (config('app.env') === 'production') {
            $this->info('🔐 Verificando SSL/TLS...');
            $sslIssues = $this->checkSSL();
            if (!empty($sslIssues)) {
                $vulnerabilities['ssl'] = $sslIssues;
                $this->error('❌ Problemas de SSL encontrados:');
                foreach ($sslIssues as $issue) {
                    $this->line('   • ' . $issue);
                }
            } else {
                $this->info('✅ SSL/TLS configurado correctamente');
            }
        }

        // 5. Verificar variables de entorno sensibles
        $this->info('🔑 Verificando variables de entorno...');
        $envIssues = $this->checkEnvironmentVariables();
        if (!empty($envIssues)) {
            $vulnerabilities['environment'] = $envIssues;
            $this->warn('⚠️  Advertencias de variables de entorno:');
            foreach ($envIssues as $issue) {
                $this->line('   • ' . $issue);
            }
        } else {
            $this->info('✅ Variables de entorno configuradas correctamente');
        }

        $this->newLine();

        // Generar reporte
        $reportPath = $this->generateReport($vulnerabilities);
        
        // Resumen
        if (empty($vulnerabilities)) {
            $this->info('🎉 Auditoría completada: Sin vulnerabilidades detectadas');
            $this->info("📄 Reporte generado: {$reportPath}");
            return 0;
        } else {
            $this->error('⚠️  Auditoría completada: ' . count($vulnerabilities) . ' categorías con problemas');
            $this->error("📄 Reporte generado: {$reportPath}");
            
            // Enviar alertas si se solicitó
            if ($this->option('send-alerts')) {
                $this->info('📧 Enviando alertas de seguridad...');
                $this->alertService->sendAlert('security_audit_failed', [
                    'vulnerabilities' => $vulnerabilities,
                    'count' => count($vulnerabilities),
                    'report_path' => $reportPath
                ]);
            }
            
            return 1;
        }
    }

    /**
     * Audita dependencias de Composer
     *
     * @return array
     */
    protected function auditComposerDependencies(): array
    {
        $vulnerabilities = [];

        try {
            exec('composer audit --format=json 2>&1', $output, $returnCode);
            
            if ($returnCode !== 0 && !empty($output)) {
                $result = json_decode(implode('', $output), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($result['advisories'])) {
                    $vulnerabilities = $result['advisories'];
                }
            }
        } catch (\Exception $e) {
            $vulnerabilities['error'] = 'No se pudo ejecutar composer audit: ' . $e->getMessage();
        }

        return $vulnerabilities;
    }

    /**
     * Verifica configuración de seguridad
     *
     * @return array
     */
    protected function checkSecurityConfig(): array
    {
        $issues = [];

        if (config('app.debug') === true && config('app.env') === 'production') {
            $issues[] = 'APP_DEBUG está habilitado en producción';
        }

        if (config('session.secure') === false && config('app.env') === 'production') {
            $issues[] = 'SESSION_SECURE_COOKIE está deshabilitado en producción';
        }

        if (config('session.driver') === 'file' && config('app.env') === 'production') {
            $issues[] = 'SESSION_DRIVER es "file" en producción (recomendado: Redis)';
        }

        if (config('session.same_site') !== 'none' && config('app.env') === 'production') {
            $issues[] = 'SESSION_SAME_SITE no está configurado como "none" para cross-origin';
        }

        if (!config('app.key')) {
            $issues[] = 'APP_KEY no está configurada';
        }

        return $issues;
    }

    /**
     * Verifica permisos de archivos
     *
     * @return array
     */
    protected function checkFilePermissions(): array
    {
        $issues = [];

        $paths = [
            storage_path() => '755',
            base_path('.env') => '600',
            base_path('bootstrap/cache') => '755',
        ];

        foreach ($paths as $path => $expectedPerms) {
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -3);
                if ($perms > $expectedPerms) {
                    $issues[] = "{$path} tiene permisos {$perms} (recomendado: {$expectedPerms})";
                }
            }
        }

        return $issues;
    }

    /**
     * Verifica configuración SSL
     *
     * @return array
     */
    protected function checkSSL(): array
    {
        $issues = [];
        $url = config('app.url');

        if (!str_starts_with($url, 'https://')) {
            $issues[] = 'APP_URL no usa HTTPS';
        }

        if (config('session.secure') === false) {
            $issues[] = 'SESSION_SECURE_COOKIE debe estar habilitado con HTTPS';
        }

        return $issues;
    }

    /**
     * Verifica variables de entorno sensibles
     *
     * @return array
     */
    protected function checkEnvironmentVariables(): array
    {
        $issues = [];

        // Verificar que las credenciales no estén vacías en producción
        if (config('app.env') === 'production') {
            if (empty(config('database.connections.mysql.password'))) {
                $issues[] = 'DB_PASSWORD está vacía en producción';
            }

            if (empty(config('mail.password'))) {
                $issues[] = 'MAIL_PASSWORD está vacía en producción';
            }
        }

        return $issues;
    }

    /**
     * Genera reporte de auditoría
     *
     * @param array $vulnerabilities
     * @return string
     */
    protected function generateReport(array $vulnerabilities): string
    {
        $reportPath = storage_path('logs/security-audit-' . now()->format('Y-m-d-H-i-s') . '.json');
        
        $report = [
            'timestamp' => now()->toDateTimeString(),
            'environment' => config('app.env'),
            'status' => empty($vulnerabilities) ? 'PASS' : 'FAIL',
            'vulnerabilities_count' => count($vulnerabilities),
            'vulnerabilities' => $vulnerabilities,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server' => php_uname()
            ]
        ];

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // También log en el canal de seguridad
        Log::channel('security')->info('Auditoría de seguridad ejecutada', [
            'status' => $report['status'],
            'vulnerabilities_count' => $report['vulnerabilities_count'],
            'report_path' => $reportPath
        ]);

        return $reportPath;
    }
}
