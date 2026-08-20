<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@ai-support.test',
            ],
            [
                'name' => 'System Admin',
                'password' => 'password123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'agent@ai-support.test',
            ],
            [
                'name' => 'Support Agent',
                'password' => 'password123',
                'role' => 'agent',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'customer@ai-support.test',
            ],
            [
                'name' => 'Test Customer',
                'password' => 'password123',
                'role' => 'customer',
                'is_active' => true,
            ]
        );
    }
}