<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixDirectorPermissions extends Command
{
    protected $signature = 'roles:fix-director-permissions';
    protected $description = 'Corregir permisos del Director para que solo tenga los permisos correctos';

    public function handle()
    {
        $this->info('🔧 Corrigiendo permisos del Director...');

        $directorRole = Role::where('name', 'Director')->first();

        if (!$directorRole) {
            $this->error('❌ El rol "Director" no existe');
            return 1;
        }

        // Permisos que el Director SÍ debe tener
        $allowedPermissions = [
            'purchase_plans.view',
            'purchase_plans.send',
            'purchase_plans.export',
            'purchase_plans.upload_decreto',
            'purchase_plans.upload_form_f1',
            'purchase_plans.by_year'
        ];

        // Permisos que el Director NO debe tener
        $forbiddenPermissions = [
            'purchase_plans.list',
            'purchase_plans.create',
            'purchase_plans.edit',
            'purchase_plans.delete',
            'purchase_plans.visar',
            'purchase_plans.approve',
            'purchase_plans.reject'
        ];

        // Revocar todos los permisos actuales
        $this->info('🔄 Revocando todos los permisos actuales...');
        $directorRole->revokePermissionTo(Permission::all());

        // Asignar solo los permisos permitidos
        $this->info('✅ Asignando permisos correctos...');
        foreach ($allowedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $directorRole->givePermissionTo($permission);
                $this->line("  ✅ Asignado: {$permissionName}");
            } else {
                $this->warn("  ⚠️ Permiso no encontrado: {$permissionName}");
            }
        }

        // Verificar que no tenga permisos prohibidos
        $this->info('🔍 Verificando que no tenga permisos prohibidos...');
        foreach ($forbiddenPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && $directorRole->hasPermissionTo($permission)) {
                $this->error("  ❌ Aún tiene permiso prohibido: {$permissionName}");
            } else {
                $this->line("  ✅ Sin permiso prohibido: {$permissionName}");
            }
        }

        // Mostrar permisos finales
        $this->info('📋 Permisos finales del Director:');
        $finalPermissions = $directorRole->permissions;
        foreach ($finalPermissions as $permission) {
            $this->line("  • {$permission->name}");
        }

        $this->info('✅ Permisos del Director corregidos exitosamente');
        return 0;
    }
}
