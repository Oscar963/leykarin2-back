<?php

namespace Database\Seeders;

use App\Models\StatusItemPurchase;
use Illuminate\Database\Seeder;

class StatusItemPurchasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatusItemPurchase::create([
            'name' => 'Solicitado',
        ]);

        StatusItemPurchase::create([
            'name' => 'Proceso de compra',
        ]);

        StatusItemPurchase::create([
            'name' => 'Comprado sin recepción del producto',
        ]);

        StatusItemPurchase::create([
            'name' => 'Comprado con recepción del producto',
        ]);

        StatusItemPurchase::create([
            'name' => 'Pagado',
        ]);

        StatusItemPurchase::create([
            'name' => 'Rendido',
        ]);
    }
}
