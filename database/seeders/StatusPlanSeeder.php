<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StatusPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('status_plans')->insert([
            ['name' => 'Borrador'],
            ['name' => 'Para aprobación'],
            ['name' => 'Aprobado'],
            ['name' => 'Decretado'],
            ['name' => 'Publicado'],
        ]);
    }
}
