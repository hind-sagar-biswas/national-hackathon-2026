<?php

namespace App\Services\Banking;

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\DepositRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Banking\DepositConfirmedNotification;
use App\Notifications\Banking\DepositFailedNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service class managing multi-channel external deposit requests, compliance approvals, and ledger crediting.
 */
class DepositService
{
    /**
     * Create a new DepositService instance.
     *
     * @param  TransferService  $transferService  The core double-entry transfer engine
     */
    public function __construct(
        protected TransferService $transferService,
    ) {}

    /**
     * Initiate an external deposit request for a user wallet.
     *
     * @param  User  $user  The user requesting the deposit
     * @param  DepositProvider  $provider  Payment channel/provider (bKash, Nagad, Bank, Cash)
     * @param  string  $providerRef  Transaction reference or deposit slip ID from provider
     * @param  int  $amount  The requested deposit amount in smallest currency units (paisa/cents)
     * @return DepositRequest The newly created pending deposit request entity
     *
     * @throws RuntimeException If deposit amount is not positive
     */
    public function initiate(
        User $user,
        DepositProvider $provider,
        string $providerRef,
        int $amount,
    ): DepositRequest {
        if ($amount <= 0) {
            throw new RuntimeException('Deposit amount must be greater than zero.');
        }

        return DepositRequest::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_ref' => trim($providerRef),
            'amount' => $amount,
            'status' => DepositStatus::PENDING,
        ]);
    }

    /**
     * Confirm a deposit request by debiting cash_reserve asset and crediting user wallet.
     *
     * @param  DepositRequest  $depositRequest  The pending deposit request entity to confirm
     * @param  string|null  $idempotencyKey  Optional custom idempotency key
     * @return Transaction The completed deposit transaction
     *
     * @throws RuntimeException If deposit is not pending, system account is missing, or user wallet is inactive
     */
    public function confirm(DepositRequest $depositRequest, ?string $idempotencyKey = null): Transaction
    {
        if ($depositRequest->status !== DepositStatus::PENDING) {
            throw new RuntimeException("Deposit request cannot be confirmed in '{$depositRequest->status->value}' status.");
        }

        $cashReserve = Account::where('slug', 'cash_reserve')->first();
        if (! $cashReserve) {
            throw new RuntimeException("System 'cash_reserve' account not found.");
        }

        $depositRequest->loadMissing(['user.account']);
        $userAccount = $depositRequest->user->account;
        if (! $userAccount) {
            throw new RuntimeException('User does not have an active wallet account.');
        }

        $idempotencyKey = $idempotencyKey ?: "dep_confirm_{$depositRequest->id}";

        return DB::transaction(function () use ($depositRequest, $cashReserve, $userAccount, $idempotencyKey) {
            $transaction = $this->transferService->transfer(
                fromAccount: $cashReserve,
                toAccount: $userAccount,
                amount: $depositRequest->amount,
                type: TransactionType::DEPOSIT,
                idempotencyKey: $idempotencyKey,
                initiatedByUserId: $depositRequest->user_id,
                metadata: [
                    'deposit_request_id' => $depositRequest->id,
                    'provider' => $depositRequest->provider->value,
                    'provider_ref' => $depositRequest->provider_ref,
                ],
            );

            $depositRequest->update([
                'status' => DepositStatus::CONFIRMED,
                'confirmed_at' => now(),
            ]);

            DB::afterCommit(function () use ($depositRequest) {
                $depositRequest->loadMissing('user');
                $depositRequest->user->notify(new DepositConfirmedNotification($depositRequest));
            });

            return $transaction;
        });
    }

    /**
     * Mark a deposit request as failed / rejected and dispatch notification to the user.
     *
     * @param  DepositRequest  $depositRequest  The pending deposit request entity to reject
     * @param  string|null  $reason  Optional rejection explanation for compliance audit
     *
     * @throws RuntimeException If deposit request is not pending
     */
    public function reject(DepositRequest $depositRequest, ?string $reason = null): void
    {
        if ($depositRequest->status !== DepositStatus::PENDING) {
            throw new RuntimeException("Deposit request cannot be rejected in '{$depositRequest->status->value}' status.");
        }

        DB::transaction(function () use ($depositRequest, $reason) {
            $depositRequest->update(['status' => DepositStatus::FAILED]);

            DB::afterCommit(function () use ($depositRequest, $reason) {
                $depositRequest->loadMissing('user');
                $depositRequest->user->notify(new DepositFailedNotification($depositRequest, $reason));
            });
        });
    }
}
