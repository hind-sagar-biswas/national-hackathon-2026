<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
        ]);
        Admin::create([
            'user_id' => $user->id,
            'is_super' => true,
        ]);
        $user->assignRole(Role::ADMIN);

        // Admin
        // $user = User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@test.com',
        // ]);
        // Admin::create([
        //     'user_id' => $user->id,
        // ]);
        // $user->assignRole(Role::ADMIN);
    }
}
