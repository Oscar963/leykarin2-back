<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Direction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixHierarchicalUserDirectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🔧 CORRIGIENDO USUARIOS JERÁRQUICOS CON MÚLTIPLES DIRECCIONES...');
        $this->command->newLine();

        // Obtener usuarios con roles jerárquicos (excluyendo administradores y secretaría comunal)
        $hierarchicalUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', User::HIERARCHICAL_ROLES);
        })->with(['roles', 'directions'])->get();

        $fixedCount = 0;
        $validCount = 0;
        $excludedCount = 0;

        foreach ($hierarchicalUsers as $user) {
            $directionCount = $user->directions()->count();
            $roles = $user->getRoleNames()->implode(', ');

            // Verificar si el usuario tiene roles que permiten múltiples direcciones
            if ($user->hasAnyRole(User::MULTI_DIRECTION_ROLES)) {
                $excludedCount++;
                continue;
            }

            if ($directionCount > 1) {
                $this->command->warn("🔧 Corrigiendo usuario: {$user->name} {$user->paternal_surname} ({$user->email})");
                $this->command->line("   Roles: {$roles}");
                $this->command->line("   Direcciones actuales: {$directionCount}");

                // Obtener todas las direcciones del usuario
                $directions = $user->directions;
                $firstDirection = $directions->first();
                $otherDirections = $directions->slice(1);

                // Remover todas las direcciones
                $user->directions()->detach();

                // Asignar solo la primera dirección
                if ($firstDirection) {
                    $user->directions()->attach($firstDirection->id);
                    $this->command->line("   ✅ Mantenida: {$firstDirection->name}");
                    
                    foreach ($otherDirections as $direction) {
                        $this->command->line("   ❌ Removida: {$direction->name}");
                    }
                }

                $fixedCount++;
                $this->command->newLine();
            } else {
                $validCount++;
            }
        }

        $this->command->info("📊 RESUMEN DE CORRECCIÓN:");
        $this->command->line("   • Total usuarios jerárquicos: {$hierarchicalUsers->count()}");
        $this->command->line("   • Usuarios excluidos (múltiples direcciones): {$excludedCount}");
        $this->command->line("   • Usuarios válidos: {$validCount}");
        $this->command->line("   • Usuarios corregidos: {$fixedCount}");

        if ($fixedCount > 0) {
            $this->command->info("✅ Corrección completada exitosamente");
        } else {
            $this->command->info("🎉 No se encontraron usuarios que necesitaran corrección");
        }
    }
} 