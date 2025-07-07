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
            // ===== MÓDULO DE AUTENTICACIÓN Y USUARIOS =====
            'auth.login',
            'auth.logout',
            'auth.reset_password',
            'auth.forgot_password',
            
            // Permisos de usuarios
            'users.list',
            'users.create',
            'users.edit',
            'users.delete',
            'users.view',
            'users.reset_password',
            'users.update_password',
            'users.update_profile',
            'users.profile',
        
            // ===== MÓDULO DE ROLES Y PERMISOS =====
            'roles.list',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.assign',
            
            'permissions.list',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'permissions.assign',

            // ===== MÓDULO DE INMUEBLES =====
            'inmuebles.list',
            'inmuebles.create',
            'inmuebles.edit',
            'inmuebles.delete',
            'inmuebles.view',
            'inmuebles.bulk_create',
            'inmuebles.bulk_update',
            'inmuebles.bulk_delete',
            'inmuebles.search',
            'inmuebles.filter',
            'inmuebles.statistics',
            'inmuebles.export',
            'inmuebles.custom_export',

            // ===== MÓDULO DE IMPORTACIÓN DE INMUEBLES =====
            'inmuebles.import.template',
            'inmuebles.import.column_mapping',
            'inmuebles.import.preview',
            'inmuebles.import.store',
            'inmuebles.import.statistics',
            'inmuebles.import.cancel',

            // ===== MÓDULO DE HISTORIAL DE IMPORTACIÓN =====
            'inmuebles.import_history.list',
            'inmuebles.import_history.view',
            'inmuebles.import_history.statistics',
            'inmuebles.import_history.recent_summary',
            'inmuebles.import_history.versions',
            'inmuebles.import_history.create_version',
            'inmuebles.import_history.rollback',
            'inmuebles.import_history.export',
            'inmuebles.import_history.delete',

            // ===== MÓDULO DE LOGS DE ACTIVIDAD =====
            'activity_logs.list',
            'activity_logs.view',
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Obtener los roles
        $adminSistema = Role::where('name', 'Administrador del Sistema')->first();
        $gestorContenido = Role::where('name', 'Gestor de Contenido')->first();

        // 1. Administrador del Sistema - TODOS los permisos
        $adminSistema->givePermissionTo(Permission::all());

        // 2. Gestor de Contenido - Acceso completo a inmuebles + permisos básicos
        $gestorContenido->givePermissionTo([
            // Autenticación básica
            'auth.login',
            'auth.logout',
            'users.update_profile',
            'users.profile',
            'users.update_password',
            
            // ===== MÓDULO DE INMUEBLES - ACCESO COMPLETO =====
            'inmuebles.list',
            'inmuebles.create',
            'inmuebles.edit',
            'inmuebles.delete',
            'inmuebles.view',
            'inmuebles.bulk_create',
            'inmuebles.bulk_update',
            'inmuebles.bulk_delete',
            'inmuebles.search',
            'inmuebles.filter',
            'inmuebles.statistics',
            'inmuebles.export',
            'inmuebles.custom_export',

            // ===== MÓDULO DE IMPORTACIÓN DE INMUEBLES - ACCESO COMPLETO =====
            'inmuebles.import.template',
            'inmuebles.import.column_mapping',
            'inmuebles.import.preview',
            'inmuebles.import.store',
            'inmuebles.import.statistics',
            'inmuebles.import.cancel',

            // ===== MÓDULO DE HISTORIAL DE IMPORTACIÓN - ACCESO COMPLETO =====
            'inmuebles.import_history.list',
            'inmuebles.import_history.view',
            'inmuebles.import_history.statistics',
            'inmuebles.import_history.recent_summary',
            'inmuebles.import_history.versions',
            'inmuebles.import_history.create_version',
            'inmuebles.import_history.rollback',
            'inmuebles.import_history.export',
            'inmuebles.import_history.delete',

            // ===== MÓDULO DE LOGS DE ACTIVIDAD =====
            'activity_logs.list',
            'activity_logs.view',
        ]);

        $this->command->info('Permisos creados y asignados correctamente a los roles.');
    }
}
