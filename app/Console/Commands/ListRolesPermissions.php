<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ListRolesPermissions extends Command
{
    protected $signature = 'list:roles-permissions {--roles-only} {--permissions-only} {--role=}';
    protected $description = 'Lista todos los roles y permisos del sistema';

    public function handle()
    {
        $this->info('📋 SISTEMA DE ROLES Y PERMISOS - PLAN DE COMPRAS MUNICIPAL');
        $this->line('='.str_repeat('=', 70));

        if ($this->option('permissions-only')) {
            $this->listPermissions();
            return 0;
        }

        if ($this->option('roles-only')) {
            $this->listRoles();
            return 0;
        }

        if ($this->option('role')) {
            $this->listSpecificRole($this->option('role'));
            return 0;
        }

        // Mostrar todo por defecto
        $this->listRoles();
        $this->newLine();
        $this->listPermissions();
        $this->newLine();
        $this->listRolePermissions();

        return 0;
    }

    private function listRoles()
    {
        $this->info('🎭 ROLES DEL SISTEMA:');
        $this->line('-'.str_repeat('-', 50));

        $roles = Role::withCount('permissions')->orderBy('name')->get();

        foreach ($roles as $role) {
            $this->line("✅ {$role->name} ({$role->permissions_count} permisos)");
        }
    }

    private function listPermissions()
    {
        $this->info('🔐 PERMISOS AGRUPADOS POR MÓDULO:');
        $this->line('-'.str_repeat('-', 50));

        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = [];

        foreach ($permissions as $permission) {
            $module = explode('.', $permission->name)[0];
            $groupedPermissions[$module][] = $permission->name;
        }

        foreach ($groupedPermissions as $module => $perms) {
            $moduleNames = [
                'auth' => '🔑 AUTENTICACIÓN',
                'users' => '👥 USUARIOS',
                'directions' => '🏢 DIRECCIONES',
                'purchase_plans' => '📋 PLANES DE COMPRA',
                'purchase_plan_statuses' => '📊 ESTADOS DE PLANES',
                'projects' => '🏗️ PROYECTOS',
                'goals' => '🎯 METAS (PROYECTOS ESTRATÉGICOS)',
                'item_purchases' => '🛍️ ITEMS DE COMPRA',
                'budget_allocations' => '💰 ASIGNACIONES PRESUPUESTARIAS',
                'type_purchases' => '📝 TIPOS DE COMPRA',
                'type_projects' => '🏷️ TIPOS DE PROYECTO',
                'unit_purchasings' => '🏪 UNIDADES DE COMPRA',
                'status_item_purchases' => '📈 ESTADOS DE ITEMS',
                'status_purchase_plans' => '📋 ESTADOS DE PLANES',
                'form_f1' => '📄 FORMULARIOS F1',
                'files' => '📁 ARCHIVOS',
                'history_purchase_histories' => '📚 HISTORIAL DE MOVIMIENTOS',
                'reports' => '📊 REPORTES',
                'audit' => '🔍 AUDITORÍA',
                'system' => '⚙️ CONFIGURACIÓN DEL SISTEMA',
                'roles' => '🎭 ROLES',
                'permissions' => '🔐 PERMISOS'
            ];

            $moduleName = $moduleNames[$module] ?? strtoupper($module);
            $this->line("\n{$moduleName}:");
            
            foreach ($perms as $perm) {
                $this->line("   • {$perm}");
            }
        }
    }

    private function listRolePermissions()
    {
        $this->info('🎭 ROLES Y SUS PERMISOS ASIGNADOS:');
        $this->line('-'.str_repeat('-', 50));

        $roles = Role::with('permissions')->orderBy('name')->get();

        foreach ($roles as $role) {
            $this->newLine();
            $this->line("🔹 {$role->name} ({$role->permissions->count()} permisos):");
            
            if ($role->permissions->isEmpty()) {
                $this->line("   ❌ Sin permisos asignados");
                continue;
            }

            $groupedPerms = [];
            foreach ($role->permissions as $permission) {
                $module = explode('.', $permission->name)[0];
                $groupedPerms[$module][] = $permission->name;
            }

            foreach ($groupedPerms as $module => $perms) {
                $this->line("   📂 {$module}: " . implode(', ', $perms));
            }
        }
    }

    private function listSpecificRole($roleName)
    {
        $role = Role::where('name', 'like', "%{$roleName}%")->with('permissions')->first();

        if (!$role) {
            $this->error("❌ Rol '{$roleName}' no encontrado");
            return;
        }

        $this->info("🎭 DETALLES DEL ROL: {$role->name}");
        $this->line('='.str_repeat('=', 50));
        $this->line("📊 Total de permisos: {$role->permissions->count()}");
        
        $this->newLine();
        $this->info("🔐 PERMISOS ASIGNADOS:");
        
        $groupedPerms = [];
        foreach ($role->permissions as $permission) {
            $module = explode('.', $permission->name)[0];
            $groupedPerms[$module][] = $permission->name;
        }

        foreach ($groupedPerms as $module => $perms) {
            $this->line("\n📂 {$module}:");
            foreach ($perms as $perm) {
                $this->line("   ✅ {$perm}");
            }
        }
    }
} 