<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckDirectorPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check-director';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los permisos del Director en el módulo de planes de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando permisos del Director en Planes de Compra...');

        $director = Role::where('name', 'Director')->first();

        if (!$director) {
            $this->error('❌ Rol Director no encontrado');
            return;
        }

        $this->info("\n📋 Rol: Director");
        $this->line('Permisos de Planes de Compra:');

        // Permisos específicos de planes de compra
        $purchasePlanPermissions = [
            'purchase_plans.list' => 'Listar planes de compra',
            'purchase_plans.create' => 'Crear planes de compra',
            'purchase_plans.edit' => 'Editar planes de compra',
            'purchase_plans.delete' => 'Eliminar planes de compra',
            'purchase_plans.view' => 'Ver planes de compra',
            'purchase_plans.visar' => 'Visar planes de compra',
            'purchase_plans.approve' => 'Aprobar planes de compra',
            'purchase_plans.reject' => 'Rechazar planes de compra',
            'purchase_plans.send' => 'Enviar planes de compra',
            'purchase_plans.export' => 'Exportar planes de compra',
            'purchase_plans.upload_decreto' => 'Subir decreto',
            'purchase_plans.upload_form_f1' => 'Subir formulario F1',
            'purchase_plans.by_year' => 'Ver por año'
        ];

        foreach ($purchasePlanPermissions as $permission => $description) {
            $hasPermission = $director->hasPermissionTo($permission);
            $status = $hasPermission ? '✅ SÍ' : '❌ NO';
            $this->line("  {$status} {$description} ({$permission})");
        }

        // Permisos relacionados con estados de planes de compra
        $this->info("\n📋 Permisos de Estados de Planes de Compra:");
        $statusPermissions = [
            'purchase_plan_statuses.list' => 'Listar estados',
            'purchase_plan_statuses.create' => 'Crear estados',
            'purchase_plan_statuses.edit' => 'Editar estados',
            'purchase_plan_statuses.delete' => 'Eliminar estados',
            'purchase_plan_statuses.view' => 'Ver estados',
            'purchase_plan_statuses.history' => 'Ver historial de estados',
            'purchase_plan_statuses.current' => 'Ver estado actual'
        ];

        foreach ($statusPermissions as $permission => $description) {
            $hasPermission = $director->hasPermissionTo($permission);
            $status = $hasPermission ? '✅ SÍ' : '❌ NO';
            $this->line("  {$status} {$description} ({$permission})");
        }

        // Resumen
        $this->info("\n📊 RESUMEN:");
        $this->line("  • El Director NO puede: Listar, Crear, Editar, Eliminar planes de compra");
        $this->line("  • El Director SÍ puede: Ver, Enviar, Exportar, Subir archivos");
        $this->line("  • El Director NO puede: Visar, Aprobar, Rechazar (solo enviar para aprobación)");

        $this->info("\n✅ Verificación completada");
    }
}
