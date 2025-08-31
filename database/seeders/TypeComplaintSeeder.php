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
            ['name' => 'Acoso Sexual','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Acoso Laboral','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Discriminación, Sexismo y/o Conductas Incívicas','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Violencia en el trabajo ejercida por un tercero, ajenos a la relación laboral','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
