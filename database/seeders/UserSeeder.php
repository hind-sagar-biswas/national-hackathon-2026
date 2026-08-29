<?php

namespace Database\Seeders;

use App\Enums\AccountOwner;
use App\Enums\AccountType;
use App\Enums\Role;
use App\Enums\TransactionDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\OperationEvent;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformEquity = Account::where('slug', 'platform_equity')->firstOrFail();

        $demoUsers = [
            [
                'name' => 'Rahim Ahmed',
                'email' => 'user@test.com',
                'phone' => '01700000001',
                'nid' => 'XXXXXXXX03',
                'balance_bdt' => 50000,
            ],
            [
                'name' => 'Karim Chowdhury',
                'email' => 'user2@test.com',
                'phone' => '01700000002',
                'nid' => 'XXXXXXXX04',
                'balance_bdt' => 40000,
            ],
            [
                'name' => 'Nusrat Jahan',
                'email' => 'user3@test.com',
                'phone' => '01700000003',
                'nid' => 'XXXXXXXX05',
                'balance_bdt' => 25000,
            ],
            [
                'name' => 'Tanvir Hasan',
                'email' => 'user4@test.com',
                'phone' => '01700000004',
                'nid' => 'XXXXXXXX06',
                'balance_bdt' => 60000,
            ],
            [
                'name' => 'Ayesha Siddiqua',
                'email' => 'user5@test.com',
                'phone' => '01700000005',
                'nid' => 'XXXXXXXX07',
                'balance_bdt' => 15000,
            ],
            [
                'name' => 'Gloria Jeans Coffee',
                'email' => 'merchant@test.com',
                'phone' => '01700000009',
                'nid' => 'XXXXXXXX08',
                'balance_bdt' => 10000,
            ],
        ];

        foreach ($demoUsers as $userData) {
            DB::transaction(function () use ($userData, $platformEquity) {
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'phone' => $userData['phone'],
                        'nid' => $userData['nid'],
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]
                );

                $user->assignRole(Role::USER);

                $bonusAmount = toPaisa($userData['balance_bdt']);

                $account = Account::firstOrCreate(
                    ['user_id' => $user->id, 'owner_type' => AccountOwner::USER],
                    [
                        'category' => AccountType::LIABILITY,
                        'cleared_balance' => $bonusAmount,
                        'available_balance' => $bonusAmount,
                        'currency' => 'BDT',
                        'is_system' => false,
                    ]
                );

                // If account was already existing, do not double-mint bonus
                if (! $account->wasRecentlyCreated) {
                    return;
                }

                // Debit Platform Equity
                $platformEquity->decrement('cleared_balance', $bonusAmount);
                $platformEquity->decrement('available_balance', $bonusAmount);
                $platformEquity->refresh();

                // Create initial transaction record
                $idempotencyKey = "seed_bonus_{$user->id}";
                $transaction = Transaction::create([
                    'reference' => 'REG-'.strtoupper(Str::random(10)),
                    'type' => TransactionType::REGISTRATION_BONUS,
                    'idempotency_key' => $idempotencyKey,
                    'initiated_by' => $user->id,
                    'metadata' => [
                        'bonus_amount' => $bonusAmount,
                        'user_id' => $user->id,
                        'note' => 'Initial seeded wallet balance',
                    ],
                ]);

                // Balanced 2-Leg Ledger Entries
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $platformEquity->id,
                    'direction' => TransactionDirection::DEBIT,
                    'amount' => $bonusAmount,
                    'balance_after' => $platformEquity->cleared_balance,
                ]);

                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'direction' => TransactionDirection::CREDIT,
                    'amount' => $bonusAmount,
                    'balance_after' => $account->cleared_balance,
                ]);

                OperationEvent::create([
                    'operation_key' => $idempotencyKey,
                    'status' => TransactionStatus::COMPLETED,
                    'from_account_id' => $platformEquity->id,
                    'to_account_id' => $account->id,
                    'amount' => $bonusAmount,
                    'metadata' => ['transaction_id' => $transaction->id],
                ]);
            });
        }
    }
}
