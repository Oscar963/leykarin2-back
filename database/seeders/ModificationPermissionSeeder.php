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
            'modifications.list',
            'modifications.create',
            'modifications.show',
            'modifications.edit',
            'modifications.delete',
            'modifications.update_status'
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
                'modifications.update_status'
            ],
            'Subrogante de Director' => [
                'modifications.list',
                'modifications.create',
                'modifications.show',
                'modifications.edit',
                'modifications.update_status'
            ],
            'Visador' => [
                'modifications.list',
                'modifications.show',
                'modifications.update_status'
            ],
            'Usuario' => [
                'modifications.list',
                'modifications.show'
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