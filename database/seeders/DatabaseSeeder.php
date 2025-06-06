<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(TypeUserSeeder::class);
        $this->call(RoleSeeder::class); 
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class); 
        $this->call(StatusPurchasePlanSeeder::class);
        $this->call(BudgetAllocationsSeeder::class);
        $this->call(UnitPurchasingSeeder::class);
        $this->call(DirectionSeeder::class);
        $this->call(TypePurchaseSeeder::class);
        $this->call(StatusItemPurchasesSeeder::class);
    }
}
