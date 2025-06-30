<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CompareRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:compare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compara los permisos entre roles que funcionan y el que no funciona';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Comparando permisos entre roles...');

        $rolesToCompare = [
            'Administrador del Sistema',
            'Administrador Municipal',
            'Visador o de Administrador Municipal'
        ];

        foreach ($rolesToCompare as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $this->info("\n📋 Rol: {$roleName}");
                $this->line('Permisos de planes de compra:');

                $permissions = $role->permissions->pluck('name')->filter(function ($p) {
                    return str_contains($p, 'purchase_plans');
                });

                foreach ($permissions as $permission) {
                    $this->line("  ✅ {$permission}");
                }

                if ($permissions->isEmpty()) {
                    $this->line("  ❌ No tiene permisos de planes de compra");
                }

                // Verificar específicamente el permiso de visar
                $hasVisar = $role->hasPermissionTo('purchase_plans.visar');
                $this->line("  🔍 Tiene permiso 'purchase_plans.visar': " . ($hasVisar ? '✅ SÍ' : '❌ NO'));
            } else {
                $this->error("❌ Rol '{$roleName}' no encontrado");
            }
        }

        $this->info("\n✅ Comparación completada");
    }
}
