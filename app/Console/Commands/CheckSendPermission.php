<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckSendPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el permiso de envío de planes de compra para cada rol';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando permiso de envío de planes de compra...');
        
        $rolesToCheck = [
            'Administrador del Sistema',
            'Administrador Municipal', 
            'Visador o de Administrador Municipal'
        ];
        
        foreach ($rolesToCheck as $roleName) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                $this->info("\n📋 Rol: {$roleName}");
                
                $hasSend = $role->hasPermissionTo('purchase_plans.send');
                $hasVisar = $role->hasPermissionTo('purchase_plans.visar');
                $hasApprove = $role->hasPermissionTo('purchase_plans.approve');
                
                $this->line("  🔍 Tiene permiso 'purchase_plans.send': " . ($hasSend ? '✅ SÍ' : '❌ NO'));
                $this->line("  🔍 Tiene permiso 'purchase_plans.visar': " . ($hasVisar ? '✅ SÍ' : '❌ NO'));
                $this->line("  🔍 Tiene permiso 'purchase_plans.approve': " . ($hasApprove ? '✅ SÍ' : '❌ NO'));
                
            } else {
                $this->error("❌ Rol '{$roleName}' no encontrado");
            }
        }
        
        $this->info("\n✅ Verificación completada");
    }
} 