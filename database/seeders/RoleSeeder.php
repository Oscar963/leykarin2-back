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
            ['name' => 'Administrador del Sistema', 'guard_name' => 'web'],
            ['name' => 'Administrador Municipal', 'guard_name' => 'web'],
            ['name' => 'Visador o de Administrador Municipal', 'guard_name' => 'web'],
            ['name' => 'Director', 'guard_name' => 'web'],
            ['name' => 'Subrogante de Director', 'guard_name' => 'web'],
            ['name' => 'Jefatura', 'guard_name' => 'web'],
            ['name' => 'Subrogante de Jefatura', 'guard_name' => 'web'],
            ['name' => 'Encargado de Presupuestos', 'guard_name' => 'web'],
            ['name' => 'Subrogante de Encargado de Presupuestos', 'guard_name' => 'web'],
        ]);
    }
}
