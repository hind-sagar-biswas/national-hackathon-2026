<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        $superUser = User::firstOrCreate(
            ['email' => 'super@test.com'],
            [
                'name' => 'Super Admin',
                'phone' => '01711111111',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Admin::firstOrCreate(
            ['user_id' => $superUser->id],
            ['is_super' => true]
        );

        $superUser->assignRole(Role::ADMIN);

        // 2. Operations Admin
        $opsAdmin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Operations Admin',
                'phone' => '01711111112',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Admin::firstOrCreate(
            ['user_id' => $opsAdmin->id],
            ['is_super' => false]
        );

        $opsAdmin->assignRole(Role::ADMIN);
    }
}
