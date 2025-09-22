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
        // Crear todos los permisos del sistema
        $permissions = [
            // ===== MÓDULO DE USUARIOS =====
            'users.list',
            'users.create',
            'users.edit',
            'users.delete',
            'users.view',
            'users.change_password',

            // ===== MÓDULO DE ROLES Y PERMISOS =====
            'roles.list',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'permissions.list',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // ===== MÓDULO DE INMUEBLES =====
            'inmuebles.list',
            'inmuebles.create',
            'inmuebles.edit',
            'inmuebles.delete',
            'inmuebles.view',

            // ===== MÓDULO DE IMPORTACIÓN DE INMUEBLES =====
            'inmuebles.import',
            'inmuebles.export',

            // ===== MÓDULO DE COMPLAINTS =====
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.delete',
            'complaints.view',

            // ===== MÓDULO DE LOGS DE ACTIVIDAD =====
            'activity_logs.list',
            'activity_logs.view',
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Obtener los roles
        $administrador = Role::where('name', 'Administrador del Sistema')->first();
        $gestor = Role::where('name', 'Gestor de Denuncias')->first();
        $gestorIma = Role::where('name', 'Gestor de Denuncias IMA')->first();
        $gestorDisam = Role::where('name', 'Gestor de Denuncias DISAM')->first();
        $gestorDemuce = Role::where('name', 'Gestor de Denuncias DEMUCE')->first();
        $editor = Role::where('name', 'Editor')->first();

        // 1. Administrador: todos los permisos
        $administrador->syncPermissions(Permission::all());

        // 2. Gestor: acceso completo a denuncias, sin usuarios ni roles
        $gestor->syncPermissions([
            // Complaints (todos los permisos de complaints)
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.delete',
            'complaints.view',

        ]);

        $gestorIma->syncPermissions([
            // Complaints (todos los permisos de complaints)
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.delete',
            'complaints.view',

        ]);

        $gestorDisam->syncPermissions([
            // Complaints (todos los permisos de complaints)
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.delete',
            'complaints.view',

        ]);

        $gestorDemuce->syncPermissions([
            // Complaints (todos los permisos de complaints)
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.delete',
            'complaints.view',

        ]);

        // 3. Editor: solo ver, crear y editar complaints (NO eliminar), sin usuarios/roles
        $editor->syncPermissions([
            // Complaints (solo ver, crear y editar)
            'complaints.list',
            'complaints.create',
            'complaints.edit',
            'complaints.view',
        ]);

        // Asignar permisos por defecto a todos los usuarios existentes
        $defaultPermissions = [];
        $allUsers = \App\Models\User::all();
        foreach ($allUsers as $user) {
            $user->givePermissionTo($defaultPermissions);
        }

        $this->command->info('Permisos creados y asignados correctamente a los roles.');
    }
}
