<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-roles {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los roles de un usuario específico por email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 Verificando roles del usuario: {$email}");
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario con email '{$email}' no encontrado");
            return;
        }
        
        $this->info("\n📋 Usuario: {$user->name} ({$user->email})");
        $this->line("Roles asignados:");
        
        $roles = $user->getRoleNames();
        
        if ($roles->isEmpty()) {
            $this->line("  ❌ No tiene roles asignados");
        } else {
            foreach ($roles as $role) {
                $this->line("  ✅ {$role}");
            }
        }
        
        // Verificar permisos específicos
        $this->info("\n📋 Permisos de planes de compra:");
        $permissions = [
            'purchase_plans.send' => 'Enviar planes de compra',
            'purchase_plans.export' => 'Exportar planes de compra',
            'purchase_plans.upload_decreto' => 'Subir decreto',
            'purchase_plans.upload_form_f1' => 'Subir formulario F1',
            'purchase_plans.by_year' => 'Ver por año'
        ];
        
        foreach ($permissions as $permission => $description) {
            $hasPermission = $user->hasPermissionTo($permission);
            $status = $hasPermission ? '✅ SÍ' : '❌ NO';
            $this->line("  {$status} {$description} ({$permission})");
        }
        
        $this->info("\n✅ Verificación completada");
    }
} 