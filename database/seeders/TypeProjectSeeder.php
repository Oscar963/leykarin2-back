<?php

namespace Database\Seeders;

use App\Models\TypeProject;
use Illuminate\Database\Seeder;

class TypeProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TypeProject::create([
            'name' => 'Operativo',
            'description' => 'Consumo o gasto habitual',
        ]);

        TypeProject::create([
            'name' => 'Estratégico',
            'description' => 'Vinculado a metas y objetivos de cada unidad requirente',
        ]);
    }
}
