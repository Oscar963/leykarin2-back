<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('type_complaints')->insert([
            ['key' => 'acoso_sexual', 'name' => 'Acoso Sexual'],
            ['key' => 'acoso_laboral', 'name' => 'Acoso Laboral'],
            ['key' => 'discriminacion', 'name' => ' Discriminación, Sexismo y/o Conductas Incívicas'],
            ['key' => 'violencia_terceros', 'name' => ' Violencia en el Trabajo ejercida por Tercero, ajenos a la Relación Laboral'],
        ]);
    }
}
