<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\{Plant, Resource, Department, FunctionalLocation, Equipment, User, Role, PlannerGroup};

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 🔹 Seed Plants
        $plants = collect();
        for ($i = 1; $i <= 3; $i++) {
            $plants->push(
                Plant::create([
                    'name' => 'Plant ' . $i,
                ])
            );
        }

        // 🔹 Seed Resources (tiap Plant punya 2 Resource)
        $resources = collect();
        foreach ($plants as $plant) {
            for ($i = 1; $i <= 2; $i++) {
                $resources->push(
                    Resource::create([
                        'plant_id' => $plant->id,
                        'name' => "Resource {$plant->name} - {$i}",
                    ])
                );
            }
        }

        // 🔹 Seed Departments
        $departments = collect();
        for ($i = 1; $i <= 5; $i++) {
            $departments->push(
                Department::create([
                    'name' => 'Department ' . $i,
                    'spv_id' => null,
                    'pic_id' => null,
                    'manager_id' => null,
                ])
            );
        }

        // 🔹 Seed Functional Locations (tiap Resource punya 2 FL)
        $functionalLocations = collect();
        foreach ($resources as $resource) {
            for ($i = 1; $i <= 2; $i++) {
                $functionalLocations->push(
                    FunctionalLocation::create([
                        'resources_id' => $resource->id,
                        'name' => "FL {$resource->name} - {$i}",
                    ])
                );
            }
        }

        // 🔹 Seed Equipments (tiap FL punya 3 Equipment)
        $equipments = collect();
        foreach ($functionalLocations as $fl) {
            for ($i = 1; $i <= 3; $i++) {
                $equipments->push(
                    Equipment::create([
                        'func_loc_id' => $fl->id,
                        'name' => "EQ {$fl->name} - {$i}",
                    ])
                );
            }
        }

        // 🔹 Pastikan Planner Groups ada
        $pgElektrik = PlannerGroup::firstOrCreate(['name' => 'Elektrik']);
        $pgMekanik  = PlannerGroup::firstOrCreate(['name' => 'Mekanik']);

        // 🔹 Pastikan Roles ada
        $roleSpv  = Role::firstOrCreate(['name' => 'SPV']);
        $roleUser = Role::firstOrCreate(['name' => 'User']);

        // 🔹 Seed Users (termasuk SPV)
        // SPV Elektrik
        $spvElektrik = User::create([
            'nup' => $faker->numerify('NUP###'),
            'name' => 'SPV Elektrik',
            'company' => 'GTI',
            'email' => 'spv.elektrik@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleSpv->id,
            'planner_group_id' => $pgElektrik->id,
            'dept_id' => $departments[0]->id,
            'is_defaul_password' => true,
        ]);
        $departments[0]->update(['spv_id' => $spvElektrik->id]);

        // SPV Mekanik
        $spvMekanik = User::create([
            'nup' => $faker->numerify('NUP###'),
            'name' => 'SPV Mekanik',
            'company' => 'GTI',
            'email' => 'spv.mekanik@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleSpv->id,
            'planner_group_id' => $pgMekanik->id,
            'dept_id' => $departments[1]->id,
            'is_defaul_password' => true,
        ]);
        $departments[1]->update(['spv_id' => $spvMekanik->id]);

        // User biasa
        for ($i = 1; $i <= 10; $i++) {
            $dept = $departments->random();
            User::create([
                'nup' => $faker->numerify('NUP###'),
                'name' => $faker->name,
                'company' => 'GTI',
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role_id' => $roleUser->id,
                'dept_id' => $dept->id,
                'planner_group_id' => [$pgElektrik->id, $pgMekanik->id][array_rand([0,1])],
                'is_defaul_password' => true,
            ]);
        }
    }
}
