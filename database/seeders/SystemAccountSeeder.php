<?php

namespace Database\Seeders;

use App\Enums\AccountOwner;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Seeder;

class SystemAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::firstOrCreate(
            ['slug' => 'platform_equity'],
            [
                'owner_type' => AccountOwner::SYSTEM,
                'category' => AccountType::EQUITY,
                'cleared_balance' => 0,
                'available_balance' => 0,
                'currency' => 'BDT',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['slug' => 'fee_income'],
            [
                'owner_type' => AccountOwner::SYSTEM,
                'category' => AccountType::REVENUE,
                'cleared_balance' => 0,
                'available_balance' => 0,
                'currency' => 'BDT',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['slug' => 'cash_reserve'],
            [
                'owner_type' => AccountOwner::SYSTEM,
                'category' => AccountType::ASSET,
                'cleared_balance' => 0,
                'available_balance' => 0,
                'currency' => 'BDT',
                'is_system' => true,
            ]
        );

        Account::firstOrCreate(
            ['slug' => 'users_wallet'],
            [
                'owner_type' => AccountOwner::SYSTEM,
                'category' => AccountType::LIABILITY,
                'cleared_balance' => 0,
                'available_balance' => 0,
                'currency' => 'BDT',
                'is_system' => true,
            ]
        );
    }
}
