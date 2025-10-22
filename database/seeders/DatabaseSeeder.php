<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $this->call(PlannerGroupSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(EquipmentSeeder::class);
        $this->call(FunctionalLocationSeeder::class);
        $this->call(MatsSeeder::class);
        $this->call(OrdersSeeder::class);
        $this->call(PlantSeeder::class);
        $this->call(PreventiveMaintenanceSeeder::class);
        $this->call(ResourceSeeder::class);
        $this->call(SparepartSeeder::class);
        $this->call(UserDummySeeder::class);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
