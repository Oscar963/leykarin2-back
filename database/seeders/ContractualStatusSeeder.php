<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractualStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contractual_statuses')->insert([
            ['name' => 'Planta','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contrata','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Código del trabajo','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Honorarios','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
