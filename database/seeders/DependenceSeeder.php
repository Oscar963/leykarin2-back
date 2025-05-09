<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DependenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dependences')->insert([
            ['key' => 'ima', 'name' => 'IMA'],
            ['key' => 'demuce', 'name' => 'DEMUCE'],
            ['key' => 'disam', 'name' => 'DISAM'],
        ]);
    }
}
