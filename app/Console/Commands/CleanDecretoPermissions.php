<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CleanDecretoPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'decreto:clean-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remover permisos de decreto de roles no autorizados';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Limpiando permisos de decreto de roles no autorizados...');

        // Permisos de upload/creación que deben ser removidos de ciertos roles
        $uploadPermissions = [
            'decretos.create',
            'decretos.edit', 
            'decretos.upload',
            'decretos.delete'
        ];

        // Roles que NO deben poder subir decretos
        $restrictedRoles = [
            'Secretaría Comunal de Planificación',
            'Visador',
            'Jefatura',
            'Subrogante de Jefatura', 
            'Encargado de Presupuestos',
            'Subrogante Encargado de Presupuestos'
        ];

        foreach ($restrictedRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($uploadPermissions as $permission) {
                    if ($role->hasPermissionTo($permission)) {
                        $role->revokePermissionTo($permission);
                        $this->line("✓ Removido permiso '{$permission}' del rol: {$roleName}");
                    }
                }
            }
        }

        $this->info('');
        $this->info('✅ Limpieza completada. Solo estos roles pueden subir decretos:');
        $this->line('  ✓ Administrador del Sistema');
        $this->line('  ✓ Administrador Municipal');
        $this->line('  ✓ Director');
        $this->line('  ✓ Subrogante de Director');

        return Command::SUCCESS;
    }
} 