<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Models\User;

class MigrateRoleNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:role-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los nombres de roles de Secretaría Comunal de Planificación a Encargado de Presupuestos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando migración de nombres de roles...');
        $this->newLine();

        // Migrar rol principal
        $oldRole = Role::where('name', 'Secretaría Comunal de Planificación')->first();
        if ($oldRole) {
            $this->info('📝 Migrando rol: Secretaría Comunal de Planificación → Encargado de Presupuestos');
            
            // Obtener usuarios con el rol antiguo
            $users = User::role('Secretaría Comunal de Planificación')->get();
            $this->info("   👥 Usuarios afectados: {$users->count()}");
            
            foreach ($users as $user) {
                $this->line("      • {$user->name} {$user->paternal_surname} ({$user->email})");
            }
            
            // Cambiar nombre del rol
            $oldRole->update(['name' => 'Encargado de Presupuestos']);
            $this->info('   ✅ Rol migrado correctamente');
        } else {
            $this->warn('⚠️  No se encontró el rol "Secretaría Comunal de Planificación"');
        }

        $this->newLine();

        // Migrar rol subrogante
        $oldSubroganteRole = Role::where('name', 'Subrogante de Secretaría Comunal de Planificación')->first();
        if ($oldSubroganteRole) {
            $this->info('📝 Migrando rol: Subrogante de Secretaría Comunal de Planificación → Subrogante de Encargado de Presupuestos');
            
            // Obtener usuarios con el rol antiguo
            $users = User::role('Subrogante de Secretaría Comunal de Planificación')->get();
            $this->info("   👥 Usuarios afectados: {$users->count()}");
            
            foreach ($users as $user) {
                $this->line("      • {$user->name} {$user->paternal_surname} ({$user->email})");
            }
            
            // Cambiar nombre del rol
            $oldSubroganteRole->update(['name' => 'Subrogante de Encargado de Presupuestos']);
            $this->info('   ✅ Rol migrado correctamente');
        } else {
            $this->warn('⚠️  No se encontró el rol "Subrogante de Secretaría Comunal de Planificación"');
        }

        $this->newLine();

        // Verificar roles finales
        $this->info('🔍 Verificando roles finales:');
        $roles = Role::all();
        foreach ($roles as $role) {
            $userCount = User::role($role->name)->count();
            $this->line("   • {$role->name} ({$userCount} usuarios)");
        }

        $this->newLine();
        $this->info('✅ Migración de nombres de roles completada');
        $this->info('💡 Recuerda ejecutar: php artisan permission:cache-reset');
    }
} 