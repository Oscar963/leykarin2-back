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
            ['name' => 'Gestor de Contenido', 'guard_name' => 'web'],
            ['name' => 'Editor', 'guard_name' => 'web'],
        ]);
    }
}
