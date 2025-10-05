<?php

namespace Database\Seeders;

use App\Models\PreventiveMaintenance;
use Illuminate\Database\Seeder;

class PreventiveMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PreventiveMaintenance::factory()->count(10)->create();
    }
}
