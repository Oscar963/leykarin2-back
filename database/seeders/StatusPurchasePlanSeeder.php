<?php

namespace Database\Seeders;

use App\Models\StatusPurchasePlan;
use Illuminate\Database\Seeder;

class StatusPurchasePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatusPurchasePlan::create([
            'name' => 'Borrador',
        ]);

        StatusPurchasePlan::create([
            'name' => 'Para aprobación',
        ]);

        StatusPurchasePlan::create([
            'name' => 'Aprobado',
        ]);

        StatusPurchasePlan::create([
            'name' => 'Rechazado',
        ]);

        StatusPurchasePlan::create([
            'name' => 'Decretado',
        ]);

        StatusPurchasePlan::create([
            'name' => 'Publicado',
        ]);
    }
} 