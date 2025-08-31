<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeDependencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('type_dependencies')->insert([
            ['name' => 'IMA','code' => 'I','email_notification' => 'oscar.apata@municipalidadarica.cl','created_at' => now(), 'updated_at' => now()],
            ['name' => 'DEMUCE','code' => 'C','email_notification' => 'oscar.apata@municipalidadarica.cl','created_at' => now(), 'updated_at' => now()],
            ['name' => 'DISAM','code' => 'D','email_notification' => 'oscar.apata@municipalidadarica.cl','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
