<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'active' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Consulta',
            'email' => 'consulta@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'consulta',
            'active' => true,
        ]);
    }
}
