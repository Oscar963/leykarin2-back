<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('work_relationships')->insert([
            ['name' => 'Sí', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'No', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ocasionalmente', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
