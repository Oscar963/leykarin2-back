<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GoalPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear permisos para metas
        $permissions = [
            'goals.list' => 'Listar metas',
            'goals.create' => 'Crear metas',
            'goals.edit' => 'Editar metas',
            'goals.delete' => 'Eliminar metas',
            'goals.view' => 'Ver metas',
            'goals.update_progress' => 'Actualizar progreso de metas',
            'goals.statistics' => 'Ver estadísticas de metas'
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // Asignar permisos a roles específicos
        $this->assignPermissionsToRoles();
    }

    /**
     * Asigna permisos de metas a los roles correspondientes
     */
    private function assignPermissionsToRoles()
    {
        // Administrador del Sistema - Todos los permisos
        $adminRole = Role::where('name', 'Administrador del Sistema')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Administrador Municipal - Gestión completa
        $municipalAdminRole = Role::where('name', 'Administrador Municipal')->first();
        if ($municipalAdminRole) {
            $municipalAdminRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Visador o de Administrador Municipal - Gestión completa
        $visadorRole = Role::where('name', 'Visador o de Administrador Municipal')->first();
        if ($visadorRole) {
            $visadorRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Secretaría Comunal de Planificación - Gestión completa
        $secplanRole = Role::where('name', 'Secretaría Comunal de Planificación')->first();
        if ($secplanRole) {
            $secplanRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Director - Gestión completa de metas de sus proyectos
        $directorRole = Role::where('name', 'Director')->first();
        if ($directorRole) {
            $directorRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Subrogante de Director - Mismos permisos que Director
        $subdirectorRole = Role::where('name', 'Subrogante de Director')->first();
        if ($subdirectorRole) {
            $subdirectorRole->givePermissionTo([
                'goals.list',
                'goals.create',
                'goals.edit',
                'goals.delete',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Jefatura - Solo puede ver y actualizar progreso
        $jefaturaRole = Role::where('name', 'Jefatura')->first();
        if ($jefaturaRole) {
            $jefaturaRole->givePermissionTo([
                'goals.list',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }

        // Subrogante de Jefatura - Mismos permisos que Jefatura
        $subjefaturaRole = Role::where('name', 'Subrogante de Jefatura')->first();
        if ($subjefaturaRole) {
            $subjefaturaRole->givePermissionTo([
                'goals.list',
                'goals.view',
                'goals.update_progress',
                'goals.statistics'
            ]);
        }
    }
} 