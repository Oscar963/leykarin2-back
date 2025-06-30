<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica todos los roles existentes en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando roles existentes en el sistema...');

        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->error('❌ No se encontraron roles en el sistema');
            return;
        }

        $this->info("\n📋 Roles encontrados:");

        foreach ($roles as $role) {
            $this->line("  • {$role->name}");
        }

        $this->info("\n✅ Total de roles: " . $roles->count());
    }
}
