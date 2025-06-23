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
            ['name' => 'Alcaldía', 'alias' => 'ALCALDÍA'],
            ['name' => 'Gabinete de Alcaldía', 'alias' => 'GABINETE'],
            ['name' => 'Secretaría Comunal de Planificación', 'alias' => 'SECPLAN'],
            ['name' => 'Secretaría Municipal', 'alias' => 'SECRETARIA'],
            ['name' => '1er Juzgado de policia local', 'alias' => '1ER JUZGADO'],
            ['name' => '2do Juzgado de policia local', 'alias' => '2DO JUZGADO'],
            ['name' => '3er Juzgado de policia local', 'alias' => '3ER JUZGADO'],
            ['name' => 'Administrador Municipal', 'alias' => 'ADMINISTRADOR'],
            ['name' => 'Dirección de Control', 'alias' => 'CONTROL'],
            ['name' => 'Asesoría Jurídica', 'alias' => 'JURÍDICO'],
            ['name' => 'Dirección de Administración y Finanzas', 'alias' => 'DAF'],
            ['name' => 'Dirección de Medio Ambiente, Aseo y Ornato', 'alias' => 'DIMAO'],
            ['name' => 'Dirección Desarrollo Comunitario', 'alias' => 'DIDEC'],
            ['name' => 'Dirección de Obras Municipales', 'alias' => 'DOM'],
            ['name' => 'Dirección de Tránsito y Tranporte', 'alias' => 'TRÁNSITO'],
            ['name' => 'Dirección Seguridad Pública', 'alias' => 'DIPRESEH'],
            ['name' => 'Dirección de Desarrollo Rural', 'alias' => 'RURAL'],
            ['name' => 'Dirección de Cultura', 'alias' => 'CULTURA'],
            ['name' => 'Dirección de Turismo', 'alias' => 'TURISMO'],
            ['name' => 'Dirección de Salud Municipal', 'alias' => 'DISAM'],
            ['name' => 'Departamento Municipal de Cementerios', 'alias' => 'DEMUCE'],
        ]);

        $this->command->info('Direcciones creadas exitosamente');
    }
}
