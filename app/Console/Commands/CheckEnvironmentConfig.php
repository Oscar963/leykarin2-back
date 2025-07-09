<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CheckEnvironmentConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-environment-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la configuración del entorno y las sesiones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando configuración del entorno...');
        
        // Verificar variables de entorno críticas
        $this->checkEnvironmentVariables();
        
        // Verificar directorios de almacenamiento
        $this->checkStorageDirectories();
        
        // Verificar configuración de sesiones
        $this->checkSessionConfiguration();
        
        $this->info('✅ Verificación completada');
    }
    
    private function checkEnvironmentVariables()
    {
        $this->info('📋 Verificando variables de entorno...');
        
        $requiredVars = [
            'APP_KEY' => 'Clave de aplicación',
            'APP_NAME' => 'Nombre de la aplicación',
            'APP_ENV' => 'Entorno de la aplicación',
            'APP_DEBUG' => 'Modo debug',
            'APP_URL' => 'URL de la aplicación',
            'DB_CONNECTION' => 'Conexión de base de datos',
            'DB_HOST' => 'Host de base de datos',
            'DB_DATABASE' => 'Nombre de base de datos',
            'DB_USERNAME' => 'Usuario de base de datos',
            'SESSION_DRIVER' => 'Driver de sesión',
        ];
        
        foreach ($requiredVars as $var => $description) {
            $value = env($var);
            if ($value) {
                $this->line("  ✅ {$description}: {$value}");
            } else {
                $this->error("  ❌ {$description}: NO CONFIGURADA");
            }
        }
    }
    
    private function checkStorageDirectories()
    {
        $this->info('📁 Verificando directorios de almacenamiento...');
        
        $directories = [
            'storage/app' => 'Almacenamiento de aplicaciones',
            'storage/framework/cache' => 'Caché del framework',
            'storage/framework/sessions' => 'Sesiones del framework',
            'storage/framework/views' => 'Vistas compiladas',
            'storage/logs' => 'Logs de la aplicación',
        ];
        
        foreach ($directories as $path => $description) {
            if (File::exists($path)) {
                $this->line("  ✅ {$description}: Existe");
                
                // Verificar permisos de escritura
                if (is_writable($path)) {
                    $this->line("    ✅ Permisos de escritura: OK");
                } else {
                    $this->warn("    ⚠️ Permisos de escritura: PROBLEMA");
                }
            } else {
                $this->error("  ❌ {$description}: NO EXISTE");
            }
        }
    }
    
    private function checkSessionConfiguration()
    {
        $this->info('🔐 Verificando configuración de sesiones...');
        
        $sessionDriver = config('session.driver');
        $sessionLifetime = config('session.lifetime');
        $sessionDomain = config('session.domain');
        $sessionSecure = config('session.secure');
        $sessionSameSite = config('session.same_site');
        
        $this->line("  📊 Driver de sesión: {$sessionDriver}");
        $this->line("  ⏰ Tiempo de vida: {$sessionLifetime} minutos");
        $this->line("  🌐 Dominio: " . ($sessionDomain ?: 'null (todos)'));
        $this->line("  🔒 Solo HTTPS: " . ($sessionSecure ? 'Sí' : 'No'));
        $this->line("  🍪 Same-Site: {$sessionSameSite}");
        
        // Verificar configuración específica según el driver
        if ($sessionDriver === 'file') {
            $sessionPath = config('session.files');
            if (File::exists($sessionPath)) {
                $this->line("  ✅ Directorio de sesiones: Existe");
                if (is_writable($sessionPath)) {
                    $this->line("    ✅ Permisos de escritura: OK");
                } else {
                    $this->warn("    ⚠️ Permisos de escritura: PROBLEMA");
                }
            } else {
                $this->error("  ❌ Directorio de sesiones: NO EXISTE");
            }
        } elseif ($sessionDriver === 'database') {
            $this->line("  📊 Usando base de datos para sesiones");
            // Verificar que la tabla existe
            try {
                $tableExists = Schema::hasTable('sessions');
                if ($tableExists) {
                    $this->line("    ✅ Tabla de sesiones: Existe");
                } else {
                    $this->error("    ❌ Tabla de sesiones: NO EXISTE");
                }
            } catch (\Exception $e) {
                $this->error("    ❌ Error verificando tabla: " . $e->getMessage());
            }
        }
        
        // Verificar configuración de Sanctum
        $this->info('🛡️ Verificando configuración de Sanctum...');
        $statefulDomains = config('sanctum.stateful');
        $this->line("  🌐 Dominios stateful: " . implode(', ', $statefulDomains));
        
        $currentDomain = request()->getHost();
        if (in_array($currentDomain, $statefulDomains) || 
            collect($statefulDomains)->contains(function($domain) use ($currentDomain) {
                return str_contains($domain, '*') && fnmatch($domain, $currentDomain);
            })) {
            $this->line("  ✅ Dominio actual ({$currentDomain}): Incluido en stateful");
        } else {
            $this->warn("  ⚠️ Dominio actual ({$currentDomain}): NO incluido en stateful");
        }
    }
} 