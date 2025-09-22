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
            ['name' => 'Gestor de Denuncias', 'guard_name' => 'web'],
            ['name' => 'Gestor de Denuncias IMA', 'guard_name' => 'web'],
            ['name' => 'Gestor de Denuncias DISAM', 'guard_name' => 'web'],
            ['name' => 'Gestor de Denuncias DEMUCE', 'guard_name' => 'web'],
            ['name' => 'Editor', 'guard_name' => 'web'],
        ]);
    }
}
