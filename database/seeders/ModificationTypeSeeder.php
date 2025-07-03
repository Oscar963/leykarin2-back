<?php

namespace Database\Seeders;

use App\Models\ModificationType;
use Illuminate\Database\Seeder;

class ModificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Los tipos de modificaciones son:
     * - Eliminar (cualitativa o cuantitativa)
     * - Agregar y/o cambiar
     * - Eliminar y/o agregar
     * - Agregar
     *
     * @return void
     */
    public function run()
    {
        ModificationType::create([
            'name' => 'Agregar',
            'description' => 'Adición de nuevos elementos, características o especificaciones a un proyecto o ítem',
        ]);

        ModificationType::create([
            'name' => 'Agregar y/o cambiar',
            'description' => 'Adición de nuevos elementos o modificación de elementos existentes en un proyecto o ítem',
        ]);

        ModificationType::create([
            'name' => 'Eliminar y/o agregar',
            'description' => 'Eliminación de elementos existentes y adición de nuevos elementos en un proyecto o ítem',
        ]);

        ModificationType::create([
            'name' => 'Eliminar',
            'description' => 'Eliminación de elementos existentes en un proyecto o ítem',
        ]);
    }
}
