<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestSanctumSPA extends Command
{
    protected $signature = 'test:sanctum-spa';
    protected $description = 'Verifica la configuración de Sanctum SPA';

    public function handle()
    {
        $this->info('🔐 Verificando configuración de Sanctum SPA...');

        // 1. Verificar configuración de Sanctum
        $this->info('📋 Configuración de Sanctum:');
        $statefulDomains = config('sanctum.stateful');
        $this->info('   ✅ Dominios stateful: ' . implode(', ', $statefulDomains));
        
        $guards = config('sanctum.guard');
        $this->info('   ✅ Guards: ' . implode(', ', $guards));

        // 2. Verificar configuración de CORS
        $this->info('🌐 Configuración CORS:');
        $allowedOrigins = config('cors.allowed_origins');
        $this->info('   ✅ Orígenes permitidos: ' . implode(', ', $allowedOrigins));
        $this->info('   ✅ Soporte credenciales: ' . (config('cors.supports_credentials') ? 'Sí' : 'No'));

        // 3. Verificar configuración de sesiones
        $this->info('🍪 Configuración de Sesiones:');
        $this->info('   ✅ Driver: ' . config('session.driver'));
        $this->info('   ✅ Lifetime: ' . config('session.lifetime') . ' minutos');
        $this->info('   ✅ Secure: ' . (config('session.secure') ? 'Sí' : 'No'));
        $this->info('   ✅ HTTP Only: ' . (config('session.http_only') ? 'Sí' : 'No'));
        $this->info('   ✅ Same Site: ' . config('session.same_site'));

        // 4. Verificar usuario admin
        $admin = User::where('email', 'admin.sistema@demo.com')->first();
        if ($admin) {
            $this->info("👤 Usuario admin encontrado: {$admin->name}");
            $this->info("   ✅ Status: " . ($admin->status ? 'Activo' : 'Inactivo'));
            $this->info("   ✅ RUT: {$admin->rut}");
        } else {
            $this->error('❌ Usuario admin no encontrado');
        }

        $this->newLine();
        $this->info('🎉 Configuración verificada');
        $this->newLine();
        $this->comment('💡 Flujo correcto para Angular:');
        $this->comment('   1. GET http://localhost:8000/sanctum/csrf-cookie');
        $this->comment('   2. POST http://localhost:8000/api/login (con withCredentials: true)');
        $this->comment('   3. GET http://localhost:8000/api/user (con withCredentials: true)');
        $this->newLine();
        $this->comment('🔧 En Angular, asegúrate de usar:');
        $this->comment('   httpOptions: { withCredentials: true }');
        $this->comment('   en todas las peticiones después del CSRF cookie');
        
        return 0;
    }
} 