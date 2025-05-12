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
            ['name' => 'Administrador', 'guard_name' => 'web'],
            ['name' => 'Funcionario IMA', 'guard_name' => 'web'],
            ['name' => 'Funcionario DISAM', 'guard_name' => 'web'],
            ['name' => 'Funcionario DEMUCE', 'guard_name' => 'web'],
        ]);
    }
}
