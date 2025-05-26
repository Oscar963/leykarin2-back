<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('directions')->insert([
            // director_id = 2 (director.alcaldia@demo.com)
            ['name' => 'Alcaldía', 'alias' => 'ALCALDÍA', 'director_id' => 2],
            // director_id = 3 (director.gabinete@demo.com)
            ['name' => 'Gabinete de Alcaldía', 'alias' => 'GABINETE', 'director_id' => 3],
            // director_id = 4 (director.secplan@demo.com)
            ['name' => 'Secretaría Comunal de Planificación', 'alias' => 'SECPLAN', 'director_id' => 4],
            // director_id = 5 (director.secmunicipal@demo.com)
            ['name' => 'Secretaría Municipal', 'alias' => 'SECRETARIA', 'director_id' => 5],
            // director_id = 1 (director.juzgado@demo.com)
            ['name' => '1er Juzgado de policia local', 'alias' => '1ER JUZGADO', 'director_id' => 1],
            ['name' => '2do Juzgado de policia local', 'alias' => '2DO JUZGADO', 'director_id' => 1],
            ['name' => '3er Juzgado de policia local', 'alias' => '3ER JUZGADO', 'director_id' => 1],
            ['name' => 'Administrador Municipal', 'alias' => 'ADMINISTRADOR', 'director_id' => 1],
            ['name' => 'Dirección de Control', 'alias' => 'CONTROL', 'director_id' => 1],
            ['name' => 'Asesoría Jurídica', 'alias' => 'JURÍDICO', 'director_id' => 1],
            // director_id = 6 (director.daf@demo.com)
            ['name' => 'Dirección de Administración y Finanzas', 'alias' => 'DAF', 'director_id' => 6],
            // director_id = 7 (director.dimao@demo.com)
            ['name' => 'Dirección de Medio Ambiente, Aseo y Ornato', 'alias' => 'DIMAO', 'director_id' => 7],
            // director_id = 8 (director.didec@demo.com)
            ['name' => 'Dirección Desarrollo Comunitario', 'alias' => 'DIDEC', 'director_id' => 8],
            // director_id = 9 (director.dom@demo.com)
            ['name' => 'Dirección de Obras Municipales', 'alias' => 'DOM', 'director_id' => 9],
            // director_id = 10 (director.transito@demo.com)
            ['name' => 'Dirección de Tránsito y Tranporte', 'alias' => 'TRÁNSITO', 'director_id' => 10],
            // director_id = 11 (director.dipreseh@demo.com)
            ['name' => 'Dirección Seguridad Pública', 'alias' => 'DIPRESEH', 'director_id' => 11],
            // director_id = 12 (director.rural@demo.com)
            ['name' => 'Dirección de Desarrollo Rural', 'alias' => 'RURAL', 'director_id' => 12],
            // director_id = 13 (director.cultura@demo.com)
            ['name' => 'Dirección de Cultura', 'alias' => 'CULTURA', 'director_id' => 13],
            // director_id = 14 (director.turismo@demo.com)
            ['name' => 'Dirección de Turismo', 'alias' => 'TURISMO', 'director_id' => 14],
            // director_id = 15 (director.disam@demo.com)
            ['name' => 'Dirección de Salud Municipal', 'alias' => 'DISAM', 'director_id' => 15],
            // director_id = 16 (director.demuce@demo.com)
            ['name' => 'Departamento Municipal de Cementerios', 'alias' => 'DEMUCE', 'director_id' => 16],
        ]);

        $this->command->info('Direcciones creadas exitosamente');
    }
}
