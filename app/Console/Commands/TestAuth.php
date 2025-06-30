<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestAuth extends Command
{
    protected $signature = 'test:auth';
    protected $description = 'Test authentication functionality';

    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de autenticación...');

        // Obtener primer usuario activo
        $user = User::where('status', true)->first();
        
        if (!$user) {
            $this->error('❌ No hay usuarios activos en la base de datos');
            return;
        }

        $this->info("✅ Usuario encontrado: {$user->name} (RUT: {$user->rut})");

        // Actualizar contraseña a 'password123'
        $user->password = Hash::make('password123');
        $user->save();
        $this->info("🔑 Contraseña actualizada a 'password123'");

        // Probar autenticación
        $credentials = [
            'rut' => $user->rut,
            'password' => 'password123'
        ];

        if (Auth::attempt($credentials)) {
            $this->info("✅ Auth::attempt exitoso");
            Auth::logout();
            
            $this->info("\n📝 Puedes usar estas credenciales para el login:");
            $this->info("RUT: {$user->rut}");
            $this->info("Password: password123");
            
        } else {
            $this->error("❌ Auth::attempt falló");
        }
    }
} 