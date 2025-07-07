<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CheckEnvironmentConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:check-env {--fix : Intentar corregir valores faltantes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar configuración de variables de entorno';

    /**
     * Variables críticas que deben estar configuradas
     */
    protected $criticalVars = [
        'APP_KEY',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_DATABASE',
        'DB_USERNAME',
        'CORS_ALLOWED_ORIGINS',
    ];

    /**
     * Variables recomendadas para seguridad
     */
    protected $securityVars = [
        'SECURITY_LOG_CHANNEL',
        'SECURITY_LOG_LEVEL',
        'SECURITY_MONITORING_ENABLED',
        'SECURITY_ALERT_EMAIL',
        'RATE_LIMIT_LOGIN',
        'RATE_LIMIT_RESET_PASSWORD',
        'RATE_LIMIT_LOGOUT',
        'MAX_FILE_SIZE_PDF',
        'MAX_FILE_SIZE_IMAGE',
        'MAX_FILE_SIZE_DOCUMENT',
    ];

    /**
     * Variables opcionales pero útiles
     */
    protected $optionalVars = [
        'FRONTEND_URL',
        'NOTIFICATION_MAIL_FROM_ADDRESS',
        'NOTIFICATION_MAIL_FROM_NAME',
        'AUDIT_LOG_ENABLED',
        'AUDIT_LOG_RETENTION_DAYS',
        'STORAGE_PATH_DECRETOS',
        'STORAGE_PATH_VERIFICATIONS',
        'STORAGE_PATH_TEMPLATES',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Verificando configuración de variables de entorno...');
        $this->newLine();

        $issues = [];
        $warnings = [];
        $info = [];

        // Verificar variables críticas
        $this->info('📋 Variables Críticas:');
        foreach ($this->criticalVars as $var) {
            $value = env($var);
            if (empty($value)) {
                $issues[] = $var;
                $this->error("  ❌ {$var} - NO CONFIGURADA");
            } else {
                $this->info("  ✅ {$var} - Configurada");
            }
        }

        $this->newLine();

        // Verificar variables de seguridad
        $this->info('🔒 Variables de Seguridad:');
        foreach ($this->securityVars as $var) {
            $value = env($var);
            if (empty($value)) {
                $warnings[] = $var;
                $this->warn("  ⚠️  {$var} - NO CONFIGURADA (recomendado)");
            } else {
                $this->info("  ✅ {$var} - Configurada");
            }
        }

        $this->newLine();

        // Verificar variables opcionales
        $this->info('📝 Variables Opcionales:');
        foreach ($this->optionalVars as $var) {
            $value = env($var);
            if (empty($value)) {
                $info[] = $var;
                $this->line("  ℹ️  {$var} - NO CONFIGURADA (opcional)");
            } else {
                $this->info("  ✅ {$var} - Configurada");
            }
        }

        $this->newLine();

        // Mostrar resumen
        $this->info('📊 Resumen:');
        $this->info("  Variables críticas: " . count($this->criticalVars) . " configuradas, " . count($issues) . " faltantes");
        $this->info("  Variables de seguridad: " . count($this->securityVars) . " configuradas, " . count($warnings) . " faltantes");
        $this->info("  Variables opcionales: " . count($this->optionalVars) . " configuradas, " . count($info) . " faltantes");

        $this->newLine();

        // Mostrar valores actuales de configuración
        if ($this->option('fix')) {
            $this->showCurrentValues();
        }

        // Mostrar recomendaciones
        if (!empty($issues) || !empty($warnings)) {
            $this->showRecommendations($issues, $warnings);
        }

        // Retornar código de salida
        if (!empty($issues)) {
            $this->error('❌ Hay variables críticas sin configurar. Revisa las recomendaciones.');
            return 1;
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Hay variables de seguridad sin configurar. Considera configurarlas.');
            return 0;
        }

        $this->info('✅ Todas las variables críticas están configuradas correctamente.');
        return 0;
    }

    /**
     * Mostrar valores actuales de configuración
     */
    protected function showCurrentValues()
    {
        $this->info('🔍 Valores actuales de configuración:');
        $this->newLine();

        $configs = [
            'APP' => [
                'APP_NAME' => env('APP_NAME'),
                'APP_ENV' => env('APP_ENV'),
                'APP_DEBUG' => env('APP_DEBUG'),
                'APP_URL' => env('APP_URL'),
            ],
            'Database' => [
                'DB_CONNECTION' => env('DB_CONNECTION'),
                'DB_HOST' => env('DB_HOST'),
                'DB_PORT' => env('DB_PORT'),
                'DB_DATABASE' => env('DB_DATABASE'),
            ],
            'Security' => [
                'SECURITY_LOG_CHANNEL' => env('SECURITY_LOG_CHANNEL'),
                'SECURITY_LOG_LEVEL' => env('SECURITY_LOG_LEVEL'),
                'RATE_LIMIT_LOGIN' => env('RATE_LIMIT_LOGIN'),
                'RATE_LIMIT_RESET_PASSWORD' => env('RATE_LIMIT_RESET_PASSWORD'),
            ],
            'CORS' => [
                'CORS_ALLOWED_ORIGINS' => env('CORS_ALLOWED_ORIGINS'),
                'CORS_ALLOWED_METHODS' => env('CORS_ALLOWED_METHODS'),
                'CORS_ALLOWED_HEADERS' => env('CORS_ALLOWED_HEADERS'),
            ],
            'Storage' => [
                'MAX_FILE_SIZE_PDF' => env('MAX_FILE_SIZE_PDF'),
                'MAX_FILE_SIZE_IMAGE' => env('MAX_FILE_SIZE_IMAGE'),
                'MAX_FILE_SIZE_DOCUMENT' => env('MAX_FILE_SIZE_DOCUMENT'),
            ],
        ];

        foreach ($configs as $category => $vars) {
            $this->info("📁 {$category}:");
            foreach ($vars as $var => $value) {
                $displayValue = $value ?: 'NO CONFIGURADO';
                $this->line("  {$var}: {$displayValue}");
            }
            $this->newLine();
        }
    }

    /**
     * Mostrar recomendaciones
     */
    protected function showRecommendations($issues, $warnings)
    {
        $this->info('💡 Recomendaciones:');
        $this->newLine();

        if (!empty($issues)) {
            $this->error('Variables críticas que debes configurar:');
            foreach ($issues as $var) {
                $this->line("  - {$var}");
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('Variables de seguridad recomendadas:');
            foreach ($warnings as $var) {
                $this->line("  - {$var}");
            }
            $this->newLine();
        }

        $this->info('📖 Para más información, consulta el archivo ENV_CONFIGURACION_COMPLETA.md');
        $this->info('🔧 Comandos útiles:');
        $this->line("  - php artisan key:generate");
        $this->line("  - php artisan config:clear");
        $this->line("  - php artisan config:cache");
    }
} 