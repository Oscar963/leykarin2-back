<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-permissions {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los permisos de un usuario específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return Command::FAILURE;
        }

        $this->info("=== PERMISOS DEL USUARIO ===");
        $this->newLine();
        
        $this->info("👤 Usuario: {$user->name} {$user->paternal_surname} {$user->maternal_surname}");
        $this->info("📧 Email: {$user->email}");
        $this->info("🆔 RUT: {$user->rut}");
        $this->info("📊 Estado: " . ($user->status ? 'Activo' : 'Inactivo'));
        
        $this->newLine();
        
        // Mostrar roles
        $roles = $user->getRoleNames();
        $this->info("🎭 Roles ({$roles->count()}):");
        foreach ($roles as $role) {
            $this->line("   • {$role}");
        }
        
        $this->newLine();
        
        // Mostrar permisos
        $permissions = $user->getAllPermissions();
        $this->info("🔐 Permisos ({$permissions->count()}):");
        foreach ($permissions as $permission) {
            $this->line("   • {$permission->name}");
        }
        
        $this->newLine();
        
        // Mostrar direcciones
        $directions = $user->directions;
        $this->info("📁 Direcciones ({$directions->count()}):");
        foreach ($directions as $direction) {
            $isDirector = $direction->director_id === $user->id ? ' (DIRECTOR)' : '';
            $this->line("   • {$direction->name} ({$direction->alias}){$isDirector}");
        }
        
        $this->newLine();
        
        // Verificar permisos específicos
        $this->info("🔍 Verificación de Permisos Específicos:");
        $specificPermissions = [
            'view purchase plans',
            'create purchase plans',
            'approve purchase plans',
            'view projects',
            'verify projects',
            'manage directions'
        ];
        
        foreach ($specificPermissions as $permission) {
            $hasPermission = $user->can($permission) ? '✅' : '❌';
            $this->line("   {$hasPermission} {$permission}");
        }
        
        $this->newLine();
        
        // Verificar si es administrador
        $isAdmin = $user->hasAnyRole(['Administrador del Sistema', 'Administrador Municipal']);
        $adminStatus = $isAdmin ? '✅' : '❌';
        $this->info("👑 Es Administrador: {$adminStatus}");
        
        // Verifica los permisos de proyectos de un usuario
        $this->checkProjectPermissions($user);
        
        return Command::SUCCESS;
    }

    /**
     * Verifica los permisos de proyectos de un usuario
     */
    private function checkProjectPermissions(User $user)
    {
        $this->info("🏗️  PERMISOS DE PROYECTOS:");
        
        $projectPermissions = [
            'projects.create' => 'Crear',
            'projects.edit' => 'Editar', 
            'projects.delete' => 'Eliminar',
            'projects.view' => 'Ver',
            'projects.verification' => 'Verificar'
        ];
        
        foreach ($projectPermissions as $permission => $label) {
            $hasPermission = $user->can($permission);
            $status = $hasPermission ? '✅' : '❌';
            $this->line("   {$status} {$label}: " . ($hasPermission ? 'Sí' : 'No'));
        }
        
        $this->newLine();
    }
} 