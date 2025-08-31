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
        $this->call(RoleSeeder::class); 
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class);       
        $this->call(TypeComplaintSeeder::class);
        $this->call(TypeDependencySeeder::class);
        $this->call(ContractualStatusSeeder::class);
        $this->call(HierarchicalLevelSeeder::class);
        $this->call(WorkRelationshipSeeder::class);
        $this->call(SupervisorRelationshipSeeder::class);
    }
}
