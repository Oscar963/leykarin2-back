<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixSubroganteDirectorPermissions extends Command
{
    protected $signature = 'roles:fix-subrogante-director-permissions';
    protected $description = 'Replicar exactamente los permisos del Director al Subrogante de Director';

    public function handle()
    {
        $this->info('🔧 Replicando permisos del Director al Subrogante de Director...');

        $directorRole = Role::where('name', 'Director')->first();
        $subroganteRole = Role::where('name', 'Subrogante de Director')->first();

        if (!$directorRole) {
            $this->error('❌ El rol "Director" no existe');
            return 1;
        }

        if (!$subroganteRole) {
            $this->error('❌ El rol "Subrogante de Director" no existe');
            return 1;
        }

        // Permisos que el Director tiene (y que el Subrogante debe tener)
        $allowedPermissions = [
            'purchase_plans.list',
            'purchase_plans.view',
            'purchase_plans.send',
            'purchase_plans.export',
            'purchase_plans.upload_decreto',
            'purchase_plans.upload_form_f1',
            'purchase_plans.by_year'
        ];

        // Permisos que el Director NO tiene (y que el Subrogante tampoco debe tener)
        $forbiddenPermissions = [
            'purchase_plans.create',
            'purchase_plans.edit',
            'purchase_plans.delete',
            'purchase_plans.visar',
            'purchase_plans.approve',
            'purchase_plans.reject'
        ];

        // Revocar todos los permisos actuales del Subrogante
        $this->info('🔄 Revocando todos los permisos actuales del Subrogante...');
        $subroganteRole->revokePermissionTo(Permission::all());

        // Asignar solo los permisos permitidos
        $this->info('✅ Asignando permisos correctos al Subrogante...');
        foreach ($allowedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $subroganteRole->givePermissionTo($permission);
                $this->line("  ✅ Asignado: {$permissionName}");
            } else {
                $this->warn("  ⚠️ Permiso no encontrado: {$permissionName}");
            }
        }

        // Verificar que no tenga permisos prohibidos
        $this->info('🔍 Verificando que no tenga permisos prohibidos...');
        foreach ($forbiddenPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && $subroganteRole->hasPermissionTo($permission)) {
                $this->error("  ❌ Aún tiene permiso prohibido: {$permissionName}");
            } else {
                $this->line("  ✅ Sin permiso prohibido: {$permissionName}");
            }
        }

        // Mostrar permisos finales
        $this->info('📋 Permisos finales del Subrogante de Director:');
        $finalPermissions = $subroganteRole->permissions;
        foreach ($finalPermissions as $permission) {
            $this->line("  • {$permission->name}");
        }

        // Comparar con el Director
        $this->info('🔍 Comparando permisos con el Director:');
        $directorPermissions = $directorRole->permissions->pluck('name')->toArray();
        $subrogantePermissions = $subroganteRole->permissions->pluck('name')->toArray();

        if (
            count(array_diff($directorPermissions, $subrogantePermissions)) === 0 &&
            count(array_diff($subrogantePermissions, $directorPermissions)) === 0
        ) {
            $this->info('✅ Los permisos son idénticos entre Director y Subrogante de Director');
        } else {
            $this->warn('⚠️ Los permisos no son idénticos');
        }

        $this->info('✅ Permisos del Subrogante de Director replicados exitosamente');
        return 0;
    }
}
