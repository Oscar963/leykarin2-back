<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModificationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear permisos para modificaciones
        $permissions = [
            // Permisos básicos de modificaciones
            'modifications.list',
            'modifications.create',
            'modifications.show',
            'modifications.edit',
            'modifications.delete',
            'modifications.update_status',
            
            // Permisos específicos de estados
            'modifications.approve',
            'modifications.reject',
            'modifications.activate',
            'modifications.deactivate',
            
            // Permisos de archivos
            'modifications.attach_files',
            'modifications.detach_files',
            'modifications.download_files',
            
            // Permisos de reportes y estadísticas
            'modifications.statistics',
            'modifications.reports',
            'modifications.export',
            
            // Permisos de tipos de modificación
            'modification_types.list',
            'modification_types.create',
            'modification_types.show',
            'modification_types.edit',
            'modification_types.delete',
            'modification_types.statistics'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a roles
        $roles = [
            'Administrador del Sistema' => $permissions,
            'Administrador Municipal' => $permissions,
            'Director' => [
                'modifications.list',
                'modifications.create',
                'modifications.show',
                'modifications.edit',
                'modifications.update_status',
                'modifications.approve',
                'modifications.reject',
                'modifications.attach_files',
                'modifications.detach_files',
                'modifications.download_files',
                'modifications.statistics',
                'modifications.reports',
                'modification_types.list',
                'modification_types.show',
                'modification_types.statistics'
            ],
            'Subrogante de Director' => [
                'modifications.list',
                'modifications.create',
                'modifications.show',
                'modifications.edit',
                'modifications.update_status',
                'modifications.approve',
                'modifications.reject',
                'modifications.attach_files',
                'modifications.detach_files',
                'modifications.download_files',
                'modifications.statistics',
                'modifications.reports',
                'modification_types.list',
                'modification_types.show',
                'modification_types.statistics'
            ],
            'Visador' => [
                'modifications.list',
                'modifications.show',
                'modifications.update_status',
                'modifications.approve',
                'modifications.reject',
                'modifications.download_files',
                'modifications.statistics',
                'modification_types.list',
                'modification_types.show'
            ],
            'Usuario' => [
                'modifications.list',
                'modifications.show',
                'modifications.download_files',
                'modification_types.list',
                'modification_types.show'
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permission) {
                    $permissionModel = Permission::where('name', $permission)->first();
                    if ($permissionModel && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }

        $this->command->info('Permisos de modificaciones creados y asignados exitosamente.');
    }
} 