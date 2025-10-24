<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::firstOrCreate(
            ['name' => 'Maintenance'],
            [
                'spv_id' => null,
                'pic_id' => null,
                'manager_id' => null,
            ]
        );

        Department::firstOrCreate(
            ['name' => 'Produksi'],
            [
                'spv_id' => null,
                'pic_id' => null,
                'manager_id' => null,
            ]
        );

        Department::firstOrCreate(
            ['name' => 'QC'],
            [
                'spv_id' => null,
                'pic_id' => null,
                'manager_id' => null,
            ]
        );
    }
}
