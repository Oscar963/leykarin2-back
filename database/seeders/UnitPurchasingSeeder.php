<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitPurchasing;
class UnitPurchasingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UnitPurchasing::create([
            'name' => 'D.A.F',
        ]);

        UnitPurchasing::create([
            'name' => 'SECPLAN',
        ]);
        
        
    }
}
