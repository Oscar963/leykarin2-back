<?php

namespace Database\Seeders;

use App\Models\TypePurchase;
use Illuminate\Database\Seeder;

class TypePurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TypePurchase::create([
            'name' => 'Procedimiento concursal',
            'cod_purchase_type' => '4',
        ]);

        TypePurchase::create([
            'name' => 'Contratación directa',
            'cod_purchase_type' => '5',
        ]);

        TypePurchase::create([
            'name' => 'Procedimientos especiales de contratación',
            'cod_purchase_type' => '6',
        ]); 
        
        
    }
}
