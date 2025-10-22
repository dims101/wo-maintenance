<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Manager Maintenance
            [
                'dept_id' => 1, 'role_id' => 2, 'planner_group_id' => null,
                'name' => 'Manager Maintenance', 'nup' => 'manager-main', 'email' => 'dimas.stmp+man_main@gmail.com',
            ],

            // SPV Elektrik & Mekanik
            [
                'dept_id' => 1, 'role_id' => 3, 'planner_group_id' => 1,
                'name' => 'SPV Elektrik', 'nup' => 'spv-elektrik', 'email' => 'dimas.stmp+spv_elektrik@gmail.com',
            ],
            [
                'dept_id' => 1, 'role_id' => 3, 'planner_group_id' => 2,
                'name' => 'SPV Mekanik', 'nup' => 'spv-mekanik', 'email' => 'dimas.stmp+spv_mekanik@gmail.com',
            ],

            // PIC Elektrik & Mekanik
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 1,
                'name' => 'PIC Elektrik', 'nup' => 'pic-elektrik', 'email' => 'dimas.stmp+pic_elektrik@gmail.com',
            ],
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 2,
                'name' => 'PIC Mekanik', 'nup' => 'pic-mekanik', 'email' => 'dimas.stmp+pic_mekanik@gmail.com',
            ],

            // Team Elektrik 1 & 2
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 1,
                'name' => 'Team Elektrik 1', 'nup' => 'team-elektrik1', 'email' => 'dimas.stmp+team_elektrik1@gmail.com',
            ],
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 1,
                'name' => 'Team Elektrik 2', 'nup' => 'team-elektrik2', 'email' => 'dimas.stmp+team_elektrik2@gmail.com',
            ],

            // Team Mekanik 1 & 2
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 2,
                'name' => 'Team Mekanik 1', 'nup' => 'team-mekanik1', 'email' => 'dimas.stmp+team_mekanik1@gmail.com',
            ],
            [
                'dept_id' => 1, 'role_id' => 5, 'planner_group_id' => 2,
                'name' => 'Team Mekanik 2', 'nup' => 'team-mekanik2', 'email' => 'dimas.stmp+team_mekanik2@gmail.com',
            ],

            // SPV Produksi & QC
            [
                'dept_id' => 2, 'role_id' => 3, 'planner_group_id' => null,
                'name' => 'SPV Produksi', 'nup' => 'spv-produksi', 'email' => 'uztadz.jablinx+spv_produksi@gmail.com',
            ],
            [
                'dept_id' => 3, 'role_id' => 3, 'planner_group_id' => null,
                'name' => 'SPV QC', 'nup' => 'spv-qc', 'email' => 'uztadz.jablinx+spv_qc@gmail.com',
            ],

            // User Produksi 1 & 2
            [
                'dept_id' => 2, 'role_id' => 5, 'planner_group_id' => null,
                'name' => 'User Produksi 1', 'nup' => 'user-produksi1', 'email' => 'uztadz.jablinx+user_produksi1@gmail.com',
            ],
            [
                'dept_id' => 2, 'role_id' => 5, 'planner_group_id' => null,
                'name' => 'User Produksi 2', 'nup' => 'user-produksi2', 'email' => 'uztadz.jablinx+user_produksi2@gmail.com',
            ],

            // User QC 1 & 2
            [
                'dept_id' => 3, 'role_id' => 5, 'planner_group_id' => null,
                'name' => 'User QC 1', 'nup' => 'user-qc1', 'email' => 'uztadz.jablinx+user_qc1@gmail.com',
            ],
            [
                'dept_id' => 3, 'role_id' => 5, 'planner_group_id' => null,
                'name' => 'User QC 2', 'nup' => 'user-qc2', 'email' => 'uztadz.jablinx+user_qc2@gmail.com',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('lautan123'),
                    'status' => 'Active',
                ])
            );
        }
    }
}
