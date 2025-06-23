<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ShowDirectionRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'directions:show-rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra las reglas de dirección del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📋 REGLAS DE DIRECCIÓN DEL SISTEMA');
        $this->newLine();

        $this->info('🔒 ROLES CON DIRECCIÓN ÚNICA:');
        $this->line('   Los siguientes roles solo pueden pertenecer a UNA dirección:');
        foreach (User::HIERARCHICAL_ROLES as $role) {
            $this->line("   • {$role}");
        }

        $this->newLine();

        $this->info('🔓 ROLES CON MÚLTIPLES DIRECCIONES:');
        $this->line('   Los siguientes roles pueden pertenecer a MÚLTIPLES direcciones:');
        foreach (User::MULTI_DIRECTION_ROLES as $role) {
            $this->line("   • {$role}");
        }

        $this->newLine();

        $this->info('📊 RESUMEN DE REGLAS:');
        $this->line('   • Director: Solo 1 dirección');
        $this->line('   • Subrogante de Director: Solo 1 dirección');
        $this->line('   • Jefatura: Solo 1 dirección');
        $this->line('   • Subrogante de Jefatura: Solo 1 dirección');
        $this->line('   • Administrador del Sistema: Múltiples direcciones');
        $this->line('   • Administrador Municipal: Múltiples direcciones');
        $this->line('   • Secretaría Comunal de Planificación: Múltiples direcciones');
        $this->line('   • Subrogante de Secretaría Comunal de Planificación: Múltiples direcciones');
        $this->line('   • Visador o de Administrador Municipal: Múltiples direcciones (otros roles)');

        $this->newLine();

        $this->info('💡 COMANDOS ÚTILES:');
        $this->line('   • php artisan users:validate-hierarchical-directions --dry-run');
        $this->line('   • php artisan users:validate-hierarchical-directions --fix');
        $this->line('   • php artisan directors:show-relations');
        $this->line('   • php artisan user:check-permissions {email}');

        return Command::SUCCESS;
    }
} 