<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckPurchasePlanPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check-purchase-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los permisos de planes de compra de los roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando permisos de planes de compra...');

        $roles = ['Visador o de Administrador Municipal', 'Administrador Municipal', 'Administrador del Sistema'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $this->info("\n📋 Rol: {$roleName}");
                $this->line('Permisos de planes de compra:');

                $permissions = $role->permissions->pluck('name')->filter(function ($p) {
                    return str_contains($p, 'purchase_plans');
                });

                foreach ($permissions as $permission) {
                    $this->line("  ✅ {$permission}");
                }

                if ($permissions->isEmpty()) {
                    $this->line("  ❌ No tiene permisos de planes de compra");
                }
            } else {
                $this->error("❌ Rol '{$roleName}' no encontrado");
            }
        }

        $this->info("\n✅ Verificación completada");
    }
}
