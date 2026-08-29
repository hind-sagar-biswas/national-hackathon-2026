<?php

namespace App\Services\Banking;

use App\Enums\HoldStatus;
use App\Models\Account;
use App\Models\Hold;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HoldService
{
    /**
     * Create an active balance hold on an account.
     */
    public function createHold(
        Account $account,
        int $amount,
        string $reason,
        ?Model $reference = null
    ): Hold {
        if ($amount <= 0) {
            throw new RuntimeException('Hold amount must be greater than zero.');
        }

        return DB::transaction(function () use ($account, $amount, $reason, $reference) {
            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $account->id)->lockForUpdate()->firstOrFail();

            if ($lockedAccount->available_balance < $amount) {
                $reqFormatted = formatPaisa($amount);
                $availFormatted = formatPaisa($lockedAccount->available_balance);
                throw new RuntimeException("Insufficient available balance for hold. Required: {$reqFormatted} BDT, Available: {$availFormatted} BDT");
            }

            // Decrement available balance only; cleared balance remains unaffected
            $lockedAccount->decrement('available_balance', $amount);

            return Hold::create([
                'account_id' => $lockedAccount->id,
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'status' => HoldStatus::ACTIVE,
            ]);
        });
    }

    /**
     * Capture a hold by fulfilling the underlying transfer callback.
     */
    public function captureHold(Hold $hold, callable $transferCallback): mixed
    {
        return DB::transaction(function () use ($hold, $transferCallback) {
            /** @var Hold $lockedHold */
            $lockedHold = Hold::where('id', $hold->id)->lockForUpdate()->firstOrFail();

            if ($lockedHold->status !== HoldStatus::ACTIVE) {
                throw new RuntimeException("Hold cannot be captured because it is in '{$lockedHold->status->value}' status.");
            }

            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $lockedHold->account_id)->lockForUpdate()->firstOrFail();

            // Restore available balance temporarily so the transfer execution can debit both cleared & available
            $lockedAccount->increment('available_balance', $lockedHold->amount);
            $lockedAccount->refresh();

            // Execute the transfer logic
            $result = $transferCallback($lockedAccount);

            // Mark hold as captured
            $lockedHold->update([
                'status' => HoldStatus::CAPTURED,
                'resolved_at' => now(),
            ]);

            return $result;
        });
    }

    /**
     * Release an active hold and restore available balance.
     */
    public function releaseHold(Hold $hold): void
    {
        DB::transaction(function () use ($hold) {
            /** @var Hold $lockedHold */
            $lockedHold = Hold::where('id', $hold->id)->lockForUpdate()->firstOrFail();

            if ($lockedHold->status !== HoldStatus::ACTIVE) {
                return;
            }

            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $lockedHold->account_id)->lockForUpdate()->firstOrFail();

            // Restore available balance
            $lockedAccount->increment('available_balance', $lockedHold->amount);

            // Mark hold as released
            $lockedHold->update([
                'status' => HoldStatus::RELEASED,
                'resolved_at' => now(),
            ]);
        });
    }
}
