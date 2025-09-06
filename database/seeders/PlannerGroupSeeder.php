<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlannerGroup;

class PlannerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['name' => 'Elektrik'],
            ['name' => 'Mekanik'],
        ];

        foreach ($groups as $group) {
            PlannerGroup::firstOrCreate(['name' => $group['name']], $group);
        }
    }
}
