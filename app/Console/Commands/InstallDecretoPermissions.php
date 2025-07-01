<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallDecretoPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'decreto:install-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instalar permisos necesarios para el sistema de decretos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Instalando permisos para el sistema de decretos...');

        // Permisos para decretos
        $decretoPermissions = [
            'decretos.list' => 'Listar decretos',
            'decretos.create' => 'Crear decretos',
            'decretos.edit' => 'Editar decretos',
            'decretos.delete' => 'Eliminar decretos',
            'decretos.view' => 'Ver detalles de decretos',
            'decretos.download' => 'Descargar archivos de decretos',
            'decretos.upload' => 'Subir decretos',
        ];

        // Crear permisos
        foreach ($decretoPermissions as $permission => $description) {
            Permission::firstOrCreate(['name' => $permission]);
            $this->line("✓ Permiso creado: {$permission} - {$description}");
        }

        // Asignar permisos a roles existentes
        $this->info('Asignando permisos a roles...');

        // Administrador del Sistema - Acceso completo
        $adminSistema = Role::where('name', 'Administrador del Sistema')->first();
        if ($adminSistema) {
            $adminSistema->givePermissionTo(array_keys($decretoPermissions));
            $this->line("✓ Permisos asignados a: Administrador del Sistema");
        }

        // Administrador Municipal - Acceso completo
        $adminMunicipal = Role::where('name', 'Administrador Municipal')->first();
        if ($adminMunicipal) {
            $adminMunicipal->givePermissionTo(array_keys($decretoPermissions));
            $this->line("✓ Permisos asignados a: Administrador Municipal");
        }

        // Director - Upload y visualización (puede subir decretos)
        $director = Role::where('name', 'Director')->first();
        if ($director) {
            $director->givePermissionTo([
                'decretos.list',
                'decretos.view', 
                'decretos.download',
                'decretos.upload',
                'decretos.create',
                'decretos.edit'
            ]);
            $this->line("✓ Permisos asignados a: Director (puede subir decretos)");
        }

        // Subrogante de Director - Upload y visualización (puede subir decretos)
        $subroganteDirector = Role::where('name', 'Subrogante de Director')->first();
        if ($subroganteDirector) {
            $subroganteDirector->givePermissionTo([
                'decretos.list',
                'decretos.view',
                'decretos.download', 
                'decretos.upload',
                'decretos.create',
                'decretos.edit'
            ]);
            $this->line("✓ Permisos asignados a: Subrogante de Director (puede subir decretos)");
        }

        // NOTA: Otros roles NO pueden subir decretos
        $this->info('');
        $this->line('🚫 Roles SIN permisos de subida de decretos:');
        $this->line('  - Secretaría Comunal de Planificación');
        $this->line('  - Visador');
        $this->line('  - Jefatura');
        $this->line('  - Subrogante de Jefatura');
        $this->line('  - Encargado de Presupuestos');
        $this->line('  - Subrogante Encargado de Presupuestos');

        $this->info('');
        $this->info('🎉 Sistema de decretos instalado correctamente!');
        $this->info('');
        $this->info('✅ Roles autorizados para SUBIR decretos:');
        $this->line('  ✓ Administrador del Sistema');
        $this->line('  ✓ Administrador Municipal');
        $this->line('  ✓ Director');
        $this->line('  ✓ Subrogante de Director');
        $this->info('');
        $this->info('Endpoints disponibles:');
        $this->line('  GET    /api/decretos                    # Listar decretos (todos)');
        $this->line('  POST   /api/decretos                    # Crear decreto (roles específicos)');
        $this->line('  GET    /api/decretos/{id}               # Ver decreto (todos)');
        $this->line('  PUT    /api/decretos/{id}               # Actualizar decreto (roles específicos)');
        $this->line('  DELETE /api/decretos/{id}               # Eliminar decreto (roles específicos)');
        $this->line('  GET    /api/decretos/{id}/download      # Descargar decreto (todos)');
        $this->line('  POST   /api/purchase-plans/upload/decreto-v2  # Upload mejorado (roles específicos)');
        $this->info('');
        $this->line('💡 Para limpiar permisos de otros roles ejecuta: php artisan decreto:clean-permissions');

        return Command::SUCCESS;
    }
} 