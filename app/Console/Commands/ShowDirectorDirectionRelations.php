<?php

namespace App\Console\Commands;

use App\Models\Direction;
use App\Models\User;
use Illuminate\Console\Command;

class ShowDirectorDirectionRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'directors:show-relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra las relaciones entre directores y sus direcciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== RELACIONES DIRECTOR-DIRECCIÓN ===');
        $this->newLine();

        $directions = Direction::with(['director', 'users'])->get();

        foreach ($directions as $direction) {
            $this->info("📁 DIRECCIÓN: {$direction->name} ({$direction->alias})");
            
            if ($direction->director) {
                $this->line("   👤 Director: {$direction->director->name} {$direction->director->paternal_surname} ({$direction->director->email})");
            } else {
                $this->line("   ❌ Sin director asignado");
            }

            $users = $direction->users;
            if ($users->count() > 0) {
                $this->line("   👥 Usuarios en la dirección ({$users->count()}):");
                foreach ($users as $user) {
                    $roles = $user->getRoleNames()->implode(', ');
                    $isDirector = $user->id === $direction->director_id ? ' (DIRECTOR)' : '';
                    $this->line("      • {$user->name} {$user->paternal_surname} - {$roles}{$isDirector}");
                }
            } else {
                $this->line("   ❌ Sin usuarios asignados");
            }

            $this->newLine();
        }

        $this->info('=== RESUMEN ===');
        $this->newLine();

        $totalDirections = $directions->count();
        $directionsWithDirector = $directions->whereNotNull('director_id')->count();
        $directionsWithUsers = $directions->filter(function ($direction) {
            return $direction->users->count() > 0;
        })->count();

        $this->line("📊 Total de direcciones: {$totalDirections}");
        $this->line("👤 Direcciones con director: {$directionsWithDirector}");
        $this->line("👥 Direcciones con usuarios: {$directionsWithUsers}");

        // Mostrar directores con múltiples direcciones
        $this->newLine();
        $this->info('=== DIRECTORES CON MÚLTIPLES DIRECCIONES ===');
        
        $directorsWithMultipleDirections = User::whereHas('directions', function ($query) {
            $query->havingRaw('COUNT(*) > 1');
        })->with('directions')->get();

        foreach ($directorsWithMultipleDirections as $director) {
            $directionNames = $director->directions->pluck('name')->implode(', ');
            $this->line("👤 {$director->name} {$director->paternal_surname} ({$director->email})");
            $this->line("   📁 Direcciones: {$directionNames}");
            $this->newLine();
        }

        return Command::SUCCESS;
    }
} 