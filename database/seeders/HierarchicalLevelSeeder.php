<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HierarchicalLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hierarchical_levels')->insert([
            ['name' => 'Nivel inferior', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Igual nivel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nivel superior', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Externo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
