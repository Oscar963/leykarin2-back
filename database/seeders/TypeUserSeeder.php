<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeUser;
class TypeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TypeUser::create([
            'name' => 'Administrador del Sistema',
        ]);

        TypeUser::create([
            'name' => 'Administrador Municipal',
        ]);

        TypeUser::create([
            'name' => 'Subrogante del Administrador Municipal',
        ]);

        TypeUser::create([
            'name' => 'Director',
        ]);

        TypeUser::create([
            'name' => 'Subrogante del Director',
        ]);

        TypeUser::create([
            'name' => 'Jefatura',
        ]);

        TypeUser::create([
            'name' => 'Subrogante de la Jefatura',
        ]);
        
    }
}
