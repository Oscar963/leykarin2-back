<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestPurchasePlanSendPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:purchase-plan-send-permission {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la funcionalidad de envío de planes de compra con diferentes roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $this->testSpecificUser($email);
        } else {
            $this->testAllRoles();
        }
    }

    /**
     * Prueba un usuario específico
     */
    private function testSpecificUser(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuario con email '{$email}' no encontrado.");
            return;
        }

        $this->testUserPermissions($user);
    }

    /**
     * Prueba todos los roles
     */
    private function testAllRoles()
    {
        $this->info("=== PRUEBA DE PERMISOS DE ENVÍO DE PLANES DE COMPRA ===");
        $this->newLine();

        $roles = Role::all();
        
        foreach ($roles as $role) {
            $this->info("🎭 Probando rol: {$role->name}");
            
            // Buscar un usuario con este rol
            $user = User::role($role->name)->first();
            
            if ($user) {
                $this->testUserPermissions($user);
            } else {
                $this->warn("   No se encontró usuario con el rol '{$role->name}'");
            }
            
            $this->newLine();
        }
    }

    /**
     * Prueba los permisos de un usuario específico
     */
    private function testUserPermissions(User $user)
    {
        $this->info("👤 Usuario: {$user->name} {$user->paternal_surname} {$user->maternal_surname}");
        $this->info("📧 Email: {$user->email}");
        $this->info("🎭 Roles: " . $user->getRoleNames()->implode(', '));
        
        // Verificar permisos específicos
        $hasSendPermission = $user->can('purchase_plans.send');
        $hasApprovePermission = $user->can('purchase_plans.approve');
        
        $this->info("🔐 Permisos:");
        $this->line("   • purchase_plans.send: " . ($hasSendPermission ? '✅ Sí' : '❌ No'));
        $this->line("   • purchase_plans.approve: " . ($hasApprovePermission ? '✅ Sí' : '❌ No'));
        
        // Verificar si puede enviar según el middleware
        $canSend = $this->canSendPurchasePlan($user);
        $this->info("📤 ¿Puede enviar planes?: " . ($canSend ? '✅ Sí' : '❌ No'));
        
        if (!$canSend) {
            $this->warn("   ⚠️  Este usuario NO puede enviar planes de compra para aprobación");
        }
    }

    /**
     * Verifica si un usuario puede enviar planes según el middleware
     */
    private function canSendPurchasePlan(User $user): bool
    {
        $allowedRoles = [
            'Administrador del Sistema',
            'Administrador Municipal',
            'Director'
        ];

        return $user->hasAnyRole($allowedRoles);
    }
} 