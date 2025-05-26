<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'Administrador del sistema', 'guard_name' => 'web'],
            ['name' => 'Alcalde ', 'guard_name' => 'web'],
            ['name' => 'Administrador Municipal', 'guard_name' => 'web'],
            ['name' => 'Subrogante del Administrador Municipal', 'guard_name' => 'web'],
            ['name' => 'Director', 'guard_name' => 'web'],
            ['name' => 'Subrogante del Director', 'guard_name' => 'web'],
            ['name' => 'Jefatura', 'guard_name' => 'web'],
            ['name' => 'Subrogante de la Jefatura', 'guard_name' => 'web'],
        ]);
    }
}
