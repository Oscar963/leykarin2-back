<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Direction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateHierarchicalUserDirections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:validate-hierarchical-directions {--fix : Corregir automáticamente las violaciones} {--dry-run : Solo mostrar violaciones sin corregir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida que los usuarios con roles jerárquicos pertenezcan únicamente a una dirección';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 VALIDANDO USUARIOS JERÁRQUICOS Y SUS DIRECCIONES');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $fix = $this->option('fix');

        if ($dryRun) {
            $this->warn('🧪 MODO DRY RUN: Solo se mostrarán las violaciones sin corregir');
        } elseif ($fix) {
            $this->warn('🔧 MODO CORRECCIÓN: Se corregirán automáticamente las violaciones');
        } else {
            $this->info('📋 MODO VALIDACIÓN: Solo se mostrarán las violaciones');
        }

        $this->newLine();

        // Obtener usuarios con roles jerárquicos (excluyendo administradores y secretaría comunal)
        $hierarchicalUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', User::HIERARCHICAL_ROLES);
        })->with(['roles', 'directions'])->get();

        $this->info("👥 Total de usuarios jerárquicos encontrados: {$hierarchicalUsers->count()}");
        $this->newLine();

        $violations = [];
        $validUsers = [];
        $excludedUsers = [];

        foreach ($hierarchicalUsers as $user) {
            $directionCount = $user->directions()->count();
            $roles = $user->getRoleNames()->implode(', ');

            // Verificar si el usuario tiene roles que permiten múltiples direcciones
            if ($user->hasAnyRole(User::MULTI_DIRECTION_ROLES)) {
                $excludedUsers[] = $user;
                continue;
            }

            if ($directionCount > 1) {
                $violations[] = [
                    'user' => $user,
                    'direction_count' => $directionCount,
                    'directions' => $user->directions->pluck('name')->implode(', ')
                ];
            } else {
                $validUsers[] = $user;
            }
        }

        // Mostrar usuarios excluidos (administradores y secretaría comunal)
        if (count($excludedUsers) > 0) {
            $this->info("🔓 USUARIOS EXCLUIDOS DE LA VALIDACIÓN ({$excludedUsers->count()}):");
            $this->line("   (Pueden tener múltiples direcciones)");
            foreach ($excludedUsers as $user) {
                $direction = $user->directions->first();
                $directionName = $direction ? $user->directions->pluck('name')->implode(', ') : 'Sin dirección';
                $roles = $user->getRoleNames()->implode(', ');
                $this->line("   • {$user->name} {$user->paternal_surname} ({$user->email})");
                $this->line("     Roles: {$roles}");
                $this->line("     Direcciones: {$directionName}");
                $this->newLine();
            }
        }

        // Mostrar usuarios válidos
        if (count($validUsers) > 0) {
            $this->info("✅ USUARIOS JERÁRQUICOS VÁLIDOS ({$validUsers->count()}):");
            foreach ($validUsers as $user) {
                $direction = $user->directions->first();
                $directionName = $direction ? $direction->name : 'Sin dirección';
                $roles = $user->getRoleNames()->implode(', ');
                $this->line("   • {$user->name} {$user->paternal_surname} ({$user->email})");
                $this->line("     Roles: {$roles}");
                $this->line("     Dirección: {$directionName}");
                $this->newLine();
            }
        }

        // Mostrar violaciones
        if (count($violations) > 0) {
            $this->error("❌ VIOLACIONES ENCONTRADAS ({$violations->count()}):");
            $this->newLine();

            foreach ($violations as $violation) {
                $user = $violation['user'];
                $roles = $user->getRoleNames()->implode(', ');
                
                $this->error("   👤 Usuario: {$user->name} {$user->paternal_surname} ({$user->email})");
                $this->error("      Roles: {$roles}");
                $this->error("      Direcciones ({$violation['direction_count']}): {$violation['directions']}");
                $this->newLine();
            }

            // Corregir violaciones si se solicita
            if ($fix && !$dryRun) {
                $this->fixViolations($violations);
            } elseif ($dryRun) {
                $this->warn("🧪 En modo dry-run, no se corregirán las violaciones");
            } else {
                $this->warn("💡 Usa --fix para corregir automáticamente las violaciones");
            }
        } else {
            $this->info("🎉 ¡No se encontraron violaciones! Todos los usuarios jerárquicos cumplen con la regla.");
        }

        // Mostrar estadísticas finales
        $this->newLine();
        $this->info("📊 ESTADÍSTICAS FINALES:");
        $this->line("   • Total usuarios jerárquicos: {$hierarchicalUsers->count()}");
        $this->line("   • Usuarios excluidos (múltiples direcciones): " . count($excludedUsers));
        $this->line("   • Usuarios válidos: " . count($validUsers));
        $this->line("   • Violaciones encontradas: " . count($violations));

        return Command::SUCCESS;
    }

    /**
     * Corrige las violaciones encontradas
     */
    private function fixViolations(array $violations): void
    {
        $this->newLine();
        $this->info("🔧 CORRIGIENDO VIOLACIONES...");
        $this->newLine();

        $fixedCount = 0;

        foreach ($violations as $violation) {
            $user = $violation['user'];
            $directions = $user->directions;

            $this->line("   🔄 Corrigiendo usuario: {$user->name} {$user->paternal_surname}");

            // Estrategia: mantener la primera dirección y remover las demás
            $firstDirection = $directions->first();
            $otherDirections = $directions->slice(1);

            if ($firstDirection) {
                // Remover todas las direcciones
                $user->directions()->detach();

                // Asignar solo la primera dirección
                $user->directions()->attach($firstDirection->id);

                $this->line("      ✅ Mantenida: {$firstDirection->name}");
                
                foreach ($otherDirections as $direction) {
                    $this->line("      ❌ Removida: {$direction->name}");
                }

                $fixedCount++;
            }

            $this->newLine();
        }

        $this->info("✅ Se corrigieron {$fixedCount} violaciones exitosamente");
    }
} 