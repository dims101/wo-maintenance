<?php

namespace Database\Seeders;

use App\Models\Role;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role "Super User" jika belum ada
        Role::updateOrCreate(
            ['name' => 'Super User'],
        );
    
    }
}
