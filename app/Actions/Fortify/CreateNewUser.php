<?php

namespace App\Actions\Fortify;

use App\Enums\AccountOwner;
use App\Enums\AccountType;
use App\Enums\TransactionDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\OperationEvent;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Banking\RegistrationBonusReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user with a funded double-entry wallet.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:15', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // 1. Create User
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
            ]);

            $bonusAmount = 100000;

            // 2. Create User Wallet Account (Liability)
            $userAccount = Account::create([
                'owner_type' => AccountOwner::USER,
                'user_id' => $user->id,
                'category' => AccountType::LIABILITY,
                'cleared_balance' => $bonusAmount,
                'available_balance' => $bonusAmount,
                'currency' => 'BDT',
                'is_system' => false,
            ]);

            // 3. Ensure System Platform Equity Account exists
            /** @var Account $platformEquity */
            $platformEquity = Account::firstOrCreate(
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

            // Debit Platform Equity (decreases equity as liability is minted)
            $platformEquity->decrement('cleared_balance', $bonusAmount);
            $platformEquity->decrement('available_balance', $bonusAmount);
            $platformEquity->refresh();

            // 4. Create Transaction Record
            $idempotencyKey = "reg_bonus_{$user->id}";
            $transaction = Transaction::create([
                'reference' => 'REG-'.strtoupper(Str::random(10)),
                'type' => TransactionType::REGISTRATION_BONUS,
                'idempotency_key' => $idempotencyKey,
                'initiated_by' => $user->id,
                'metadata' => [
                    'bonus_amount' => $bonusAmount,
                    'user_id' => $user->id,
                ],
            ]);

            // 5. Insert Balanced 2-Leg Ledger Entries
            // Leg 1: Debit Platform Equity
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $platformEquity->id,
                'direction' => TransactionDirection::DEBIT,
                'amount' => $bonusAmount,
                'balance_after' => $platformEquity->cleared_balance,
            ]);

            // Leg 2: Credit User Wallet
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $userAccount->id,
                'direction' => TransactionDirection::CREDIT,
                'amount' => $bonusAmount,
                'balance_after' => $userAccount->cleared_balance,
            ]);

            // 6. Record Operation Event
            OperationEvent::create([
                'operation_key' => $idempotencyKey,
                'status' => TransactionStatus::COMPLETED,
                'from_account_id' => $platformEquity->id,
                'to_account_id' => $userAccount->id,
                'amount' => $bonusAmount,
                'metadata' => ['transaction_id' => $transaction->id],
            ]);

            // 7. Assign Default Role
            $user->assignRole(config('app.defaults.role'));

            // 8. Post-Commit Welcome Notification
            DB::afterCommit(function () use ($user, $bonusAmount) {
                $user->notify(new RegistrationBonusReceivedNotification($bonusAmount));
            });

            return $user;
        });
    }
}
