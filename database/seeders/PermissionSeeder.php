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
            
            // ===== MÓDULO DE DIRECCIONES =====
            'directions.list',
            'directions.create',
            'directions.edit',
            'directions.delete',
            'directions.view',
            
            // ===== MÓDULO DE PLANES DE COMPRA =====
            'purchase_plans.list',
            'purchase_plans.create',
            'purchase_plans.edit',
            'purchase_plans.delete',
            'purchase_plans.view',
            'purchase_plans.visar',
            'purchase_plans.approve',
            'purchase_plans.reject',
            'purchase_plans.send',
            'purchase_plans.export',
            'purchase_plans.upload_decreto',
            'purchase_plans.upload_form_f1',
            'purchase_plans.by_year',
            
            // ===== MÓDULO DE ESTADOS DE PLANES DE COMPRA =====
            'purchase_plan_statuses.list',
            'purchase_plan_statuses.create',
            'purchase_plan_statuses.edit',
            'purchase_plan_statuses.delete',
            'purchase_plan_statuses.view',
            'purchase_plan_statuses.history',
            'purchase_plan_statuses.current',
            
            // ===== MÓDULO DE PROYECTOS =====
            'projects.list',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'projects.view',
            'projects.by_purchase_plan',
            'projects.by_token',
            'projects.verification',
            'projects.verification_files',
            'projects.verification_download',
            'projects.verification_delete',
            
            // ===== MÓDULO DE ITEMS DE COMPRA =====
            'item_purchases.list',
            'item_purchases.create',
            'item_purchases.edit',
            'item_purchases.delete',
            'item_purchases.view',
            'item_purchases.update_status',
            'item_purchases.export',
            
            // ===== MÓDULO DE ASIGNACIONES PRESUPUESTARIAS =====
            'budget_allocations.list',
            'budget_allocations.create',
            'budget_allocations.edit',
            'budget_allocations.delete',
            'budget_allocations.view',
            
            // ===== MÓDULO DE TIPOS DE COMPRA =====
            'type_purchases.list',
            'type_purchases.create',
            'type_purchases.edit',
            'type_purchases.delete',
            'type_purchases.view',
            
            // ===== MÓDULO DE TIPOS DE PROYECTO =====
            'type_projects.list',
            'type_projects.create',
            'type_projects.edit',
            'type_projects.delete',
            'type_projects.view',
            
            // ===== MÓDULO DE UNIDADES DE COMPRA =====
            'unit_purchasings.list',
            'unit_purchasings.create',
            'unit_purchasings.edit',
            'unit_purchasings.delete',
            'unit_purchasings.view',
            
            // ===== MÓDULO DE ESTADOS DE ITEMS DE COMPRA =====
            'status_item_purchases.list',
            'status_item_purchases.create',
            'status_item_purchases.edit',
            'status_item_purchases.delete',
            'status_item_purchases.view',
            
            // ===== MÓDULO DE ESTADOS DE PLANES DE COMPRA =====
            'status_purchase_plans.list',
            'status_purchase_plans.create',
            'status_purchase_plans.edit',
            'status_purchase_plans.delete',
            'status_purchase_plans.view',
            
            // ===== MÓDULO DE FORMULARIOS F1 =====
            'form_f1.list',
            'form_f1.create',
            'form_f1.edit',
            'form_f1.delete',
            'form_f1.view',
            'form_f1.download',
            'form_f1.upload',
            'form_f1.remove',
            
            // ===== MÓDULO DE ARCHIVOS =====
            'files.list',
            'files.create',
            'files.edit',
            'files.delete',
            'files.view',
            'files.upload',
            'files.download',
            
            // ===== MÓDULO DE HISTORIAL DE MOVIMIENTOS =====
            'history_purchase_histories.list',
            'history_purchase_histories.view',
            'history_purchase_histories.statistics',
            'history_purchase_histories.export',
            
            // ===== MÓDULO DE REPORTES =====
            'reports.view',
            'reports.export',
            'reports.purchase_plans',
            'reports.projects',
            'reports.item_purchases',
            'reports.budget_analysis',
            
            // ===== MÓDULO DE AUDITORÍA =====
            'audit.logs',
            'audit.history',
            'audit.activity',
            
            // ===== MÓDULO DE CONFIGURACIÓN DEL SISTEMA =====
            'system.config',
            'system.backup',
            'system.restore',
            'system.maintenance',
            
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
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Obtener todos los roles
        $adminSistema = Role::where('name', 'Administrador del Sistema')->first();
        $adminMunicipal = Role::where('name', 'Administrador Municipal')->first();
        $visadorAdmin = Role::where('name', 'Visador o de Administrador Municipal')->first();
        $director = Role::where('name', 'Director')->first();
        $subroganteDirector = Role::where('name', 'Subrogante de Director')->first();
        $jefatura = Role::where('name', 'Jefatura')->first();
        $subroganteJefatura = Role::where('name', 'Subrogante de Jefatura')->first();
        $encargadoPresupuestos = Role::where('name', 'Encargado de Presupuestos')->first();
        $subroganteEncargadoPresupuestos = Role::where('name', 'Subrogante de Encargado de Presupuestos')->first();

        // Asignar permisos según jerarquía

        // 1. Administrador del Sistema - TODOS los permisos
        $adminSistema->givePermissionTo(Permission::all());

        // 2. Administrador Municipal - Sin permisos sobre Usuarios y Archivos
        $adminMunicipal->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            
            // Direcciones (gestión completa)
            'directions.list', 'directions.create', 'directions.edit', 'directions.delete', 'directions.view',
            
            // Planes de compra (gestión completa)
            'purchase_plans.list', 'purchase_plans.create', 'purchase_plans.edit', 'purchase_plans.delete', 'purchase_plans.view', 'purchase_plans.visar', 'purchase_plans.approve', 'purchase_plans.reject', 'purchase_plans.send', 'purchase_plans.export', 'purchase_plans.by_year',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.history', 'purchase_plan_statuses.current',
            
            // Proyectos (CRUD completo, sin verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.delete', 'projects.view', 'projects.by_purchase_plan',
            
            // Items de compra (solo lectura)
            'item_purchases.list', 'item_purchases.view', 'item_purchases.export',
            
            // Formulario F1 (solo descarga)
            'form_f1.list', 'form_f1.view', 'form_f1.download',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics', 'history_purchase_histories.export',
            'audit.logs', 'audit.history',
            
            // Reportes
            'reports.view', 'reports.export', 'reports.purchase_plans', 'reports.projects', 'reports.item_purchases', 'reports.budget_analysis',
        ]);

        // 3. Visador - Sin permisos sobre Usuarios y Archivos
        $visadorAdmin->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            
            // Planes de compra (solo visar, no aprobar)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.visar', 'purchase_plans.reject', 'purchase_plans.export', 'purchase_plans.by_year',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.history', 'purchase_plan_statuses.current',
            
            // Proyectos (CRUD completo, sin verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.delete', 'projects.view', 'projects.by_purchase_plan',
            
            // Items de compra (solo lectura)
            'item_purchases.list', 'item_purchases.view', 'item_purchases.export',
            
            // Formulario F1 (solo descarga)
            'form_f1.list', 'form_f1.view', 'form_f1.download',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics', 'history_purchase_histories.export',
            'audit.logs', 'audit.history',
            
            // Reportes
            'reports.view', 'reports.export', 'reports.purchase_plans', 'reports.projects', 'reports.item_purchases', 'reports.budget_analysis',
        ]);

        // 4. Encargado de Presupuestos - Con permisos de lectura de planes de compra
        $encargadoPresupuestos->givePermissionTo([
            // Autenticación básica
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',
            
            // Planes de compra (solo lectura y consulta)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.by_year', 'purchase_plans.export',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.current',
            
            // Formulario F1 (gestión completa)
            'form_f1.list', 'form_f1.create', 'form_f1.edit', 'form_f1.delete', 'form_f1.view', 'form_f1.download', 'form_f1.upload', 'form_f1.remove',
            
            // Reportes básicos
            'reports.view', 'reports.purchase_plans',
        ]);

        // 5. Subrogante de Encargado de Presupuestos - Con permisos de lectura de planes de compra
        $subroganteEncargadoPresupuestos->givePermissionTo([
            // Autenticación básica
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',
            
            // Planes de compra (solo lectura y consulta)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.by_year', 'purchase_plans.export',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.current',
            
            // Formulario F1 (gestión completa)
            'form_f1.list', 'form_f1.create', 'form_f1.edit', 'form_f1.delete', 'form_f1.view', 'form_f1.download', 'form_f1.upload', 'form_f1.remove',
            
            // Reportes básicos
            'reports.view', 'reports.purchase_plans',
        ]);

        // 6. Director - Con permisos para gestionar planes de compra de su dirección
        $director->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',

            // Planes de compra (listar, ver, enviar, exportar, subir archivos)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.send', 'purchase_plans.export',
            'purchase_plans.upload_decreto', 'purchase_plans.upload_form_f1', 'purchase_plans.by_year',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.current',

            // Proyectos (CRUD completo + verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.delete', 'projects.view', 'projects.by_purchase_plan', 'projects.by_token',
            'projects.verification', 'projects.verification_files', 'projects.verification_download', 'projects.verification_delete',
            
            // Items de compra (gestión completa de su dirección)
            'item_purchases.list', 'item_purchases.create', 'item_purchases.edit', 'item_purchases.view', 'item_purchases.update_status', 'item_purchases.export',
            
            // Formulario F1 (solo descarga)
            'form_f1.list', 'form_f1.view', 'form_f1.download',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics',
            'audit.logs', 'audit.history',
            
            // Reportes
            'reports.view', 'reports.export', 'reports.purchase_plans', 'reports.projects', 'reports.item_purchases',
        ]);

        // 7. Subrogante de Director - Con permisos para gestionar planes de compra de su dirección
        $subroganteDirector->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',
            
            // Planes de compra (listar, ver, enviar y exportar)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.send', 'purchase_plans.export', 'purchase_plans.by_year',
            'purchase_plans.upload_decreto', 'purchase_plans.upload_form_f1',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.history', 'purchase_plan_statuses.current',
            
            // Proyectos (CRUD sin eliminar + verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.view', 'projects.by_purchase_plan', 'projects.by_token',
            'projects.verification', 'projects.verification_files', 'projects.verification_download', 'projects.verification_delete',
            
            // Items de compra (gestión completa de su dirección)
            'item_purchases.list', 'item_purchases.create', 'item_purchases.edit', 'item_purchases.view', 'item_purchases.update_status', 'item_purchases.export',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics',
            'audit.logs', 'audit.history',
            
            // Reportes
            'reports.view', 'reports.export', 'reports.purchase_plans', 'reports.projects', 'reports.item_purchases',
        ]);

        // 8. Jefatura - Sin permisos sobre Usuarios y Archivos
        $jefatura->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',
            
            // Planes de compra (solo lectura)
            'purchase_plans.list', 'purchase_plans.view', 'purchase_plans.by_year',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.current',
            
            // Proyectos (CRUD sin eliminar + verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.view', 'projects.by_purchase_plan',
            'projects.verification', 'projects.verification_files', 'projects.verification_download',
            
            // Items de compra (gestión operativa)
            'item_purchases.list', 'item_purchases.create', 'item_purchases.edit', 'item_purchases.view', 'item_purchases.update_status',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics',
            'audit.logs', 'audit.history',
            
            // Reportes básicos
            'reports.view', 'reports.projects', 'reports.item_purchases',
        ]);

        // 9. Subrogante de Jefatura - Sin permisos sobre Usuarios y Archivos
        $subroganteJefatura->givePermissionTo([
            // Autenticación
            'auth.login', 'auth.logout',
            'users.update_profile', 'users.profile', 'users.update_password',
            
            // Planes de compra (gestión de su dirección)
            'purchase_plans.list', 'purchase_plans.create', 'purchase_plans.edit', 'purchase_plans.view', 'purchase_plans.send', 'purchase_plans.export', 'purchase_plans.by_year',
            'purchase_plans.upload_decreto', 'purchase_plans.upload_form_f1',
            'purchase_plan_statuses.list', 'purchase_plan_statuses.view', 'purchase_plan_statuses.history', 'purchase_plan_statuses.current',
            
            // Proyectos (CRUD sin eliminar + verificar)
            'projects.list', 'projects.create', 'projects.edit', 'projects.view', 'projects.by_purchase_plan', 'projects.by_token',
            'projects.verification', 'projects.verification_files', 'projects.verification_download', 'projects.verification_delete',
            
            // Items de compra (gestión completa de su dirección)
            'item_purchases.list', 'item_purchases.create', 'item_purchases.edit', 'item_purchases.view', 'item_purchases.update_status', 'item_purchases.export',
            
            // Configuraciones (solo lectura)
            'budget_allocations.list', 'budget_allocations.view',
            'type_purchases.list', 'type_purchases.view',
            'type_projects.list', 'type_projects.view',
            'unit_purchasings.list', 'unit_purchasings.view',
            'status_item_purchases.list', 'status_item_purchases.view',
            'status_purchase_plans.list', 'status_purchase_plans.view',
            
            // Historial y auditoría
            'history_purchase_histories.list', 'history_purchase_histories.view', 'history_purchase_histories.statistics',
            'audit.logs', 'audit.history',
            
            // Reportes
            'reports.view', 'reports.export', 'reports.purchase_plans', 'reports.projects', 'reports.item_purchases',
        ]);

        $this->command->info('Permisos creados y asignados correctamente a todos los roles.');
    }
}
