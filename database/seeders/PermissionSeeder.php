<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear o obtener el rol "Administrador" con el guard_name correcto
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web']
        );

        // Crear los permisos con el guard_name correcto
        $permissions = [
            'rol.list',
            'rol.create',
            'rol.edit',
            'rol.delete',
            'rol.export',
            'rol.import',
            'rol.file',
            'logs.list',
            'logs.create',
            'logs.edit',
            'logs.delete',
            'logs.export',
            'logs.import',
            'logs.file',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asociar todos los permisos al rol "Administrador"
        $allPermissions = Permission::all();
        $adminRole->givePermissionTo($allPermissions);

        $this->command->info('Permisos asignados correctamente al rol "Administrador".');
    }
}
