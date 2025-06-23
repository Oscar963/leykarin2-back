<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirectorDirectionRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Iniciando creación de relaciones director-dirección...');

        // Mapeo de directores con sus direcciones
        $directorDirectionMap = [
            'director.juzgado@demo.com' => [
                '1er Juzgado de policia local',
                '2do Juzgado de policia local', 
                '3er Juzgado de policia local',
                'Administrador Municipal',
                'Dirección de Control',
                'Asesoría Jurídica'
            ],
            'director.alcaldia@demo.com' => ['Alcaldía'],
            'director.gabinete@demo.com' => ['Gabinete de Alcaldía'],
            'director.secplan@demo.com' => ['Secretaría Comunal de Planificación'],
            'director.secmunicipal@demo.com' => ['Secretaría Municipal'],
            'director.daf@demo.com' => ['Dirección de Administración y Finanzas'],
            'director.dimao@demo.com' => ['Dirección de Medio Ambiente, Aseo y Ornato'],
            'director.didec@demo.com' => ['Dirección Desarrollo Comunitario'],
            'director.dom@demo.com' => ['Dirección de Obras Municipales'],
            'director.transito@demo.com' => ['Dirección de Tránsito y Tranporte'],
            'director.dipreseh@demo.com' => ['Dirección Seguridad Pública'],
            'director.rural@demo.com' => ['Dirección de Desarrollo Rural'],
            'director.cultura@demo.com' => ['Dirección de Cultura'],
            'director.turismo@demo.com' => ['Dirección de Turismo'],
            'director.disam@demo.com' => ['Dirección de Salud Municipal'],
            'director.demuce@demo.com' => ['Departamento Municipal de Cementerios'],
        ];

        $relationsCreated = 0;
        $directorIdsUpdated = 0;

        foreach ($directorDirectionMap as $directorEmail => $directionNames) {
            $director = User::where('email', $directorEmail)->first();
            
            if (!$director) {
                $this->command->warn("Director no encontrado: {$directorEmail}");
                continue;
            }

            foreach ($directionNames as $directionName) {
                $direction = Direction::where('name', $directionName)->first();
                
                if (!$direction) {
                    $this->command->warn("Dirección no encontrada: {$directionName}");
                    continue;
                }

                // Actualizar director_id en la dirección
                $direction->update(['director_id' => $director->id]);
                $directorIdsUpdated++;

                // Crear relación en la tabla pivote direction_user
                DB::table('direction_user')->updateOrInsert(
                    [
                        'direction_id' => $direction->id,
                        'user_id' => $director->id
                    ],
                    [
                        'direction_id' => $direction->id,
                        'user_id' => $director->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $relationsCreated++;

                $this->command->line("✓ {$director->name} {$director->paternal_surname} -> {$direction->name}");
            }
        }

        $this->command->info("Relaciones director-dirección creadas exitosamente:");
        $this->command->info("- Director IDs actualizados: {$directorIdsUpdated}");
        $this->command->info("- Relaciones en tabla pivote: {$relationsCreated}");
    }
} 