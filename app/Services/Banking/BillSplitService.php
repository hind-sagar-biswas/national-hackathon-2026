<?php

namespace App\Services\Banking;

use App\Enums\BillSplitMode;
use App\Enums\BillSplitParticipantStatus;
use App\Enums\BillSplitStatus;
use App\Enums\HoldStatus;
use App\Enums\RequestStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BillSplit;
use App\Models\BillSplitParticipant;
use App\Models\MoneyRequest;
use App\Models\User;
use App\Notifications\Banking\BillSplitCompletedNotification;
use App\Notifications\Banking\BillSplitFailedNotification;
use App\Notifications\Banking\BillSplitInvitationNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class BillSplitService
{
    public function __construct(
        protected TransferService $transferService,
        protected HoldService $holdService,
        protected MoneyRequestService $moneyRequestService,
    ) {}

    /**
     * Create a new multi-participant bill split.
     *
     * @param  array{title: string, total_amount: int, mode: BillSplitMode, participants: array<array{user_id: int, value?: float}>, merchant_account_id?: int|null, merchant_name?: string|null, note?: string|null, expires_in_days?: int}  $data
     */
    public function createBillSplit(User $initiator, array $data): BillSplit
    {
        $initiatorAccount = $initiator->account;
        if (! $initiatorAccount) {
            throw ValidationException::withMessages([
                'initiator' => 'Initiator wallet account not found.',
            ]);
        }

        $totalAmount = (int) $data['total_amount'];
        if ($totalAmount <= 0) {
            throw ValidationException::withMessages([
                'total_amount' => 'Total bill amount must be greater than zero.',
            ]);
        }

        $mode = $data['mode'] instanceof BillSplitMode ? $data['mode'] : BillSplitMode::from($data['mode']);
        $rawParticipants = $data['participants'] ?? [];

        // Ensure initiator is in participants list
        $hasInitiator = false;
        foreach ($rawParticipants as $p) {
            if ((int) $p['user_id'] === (int) $initiator->id) {
                $hasInitiator = true;
                break;
            }
        }
        if (! $hasInitiator) {
            $rawParticipants[] = [
                'user_id' => $initiator->id,
                'value' => 1.0,
            ];
        }

        $computedShares = $this->calculateShares($totalAmount, $mode, $rawParticipants, $initiator->id);

        $expiresInDays = (int) ($data['expires_in_days'] ?? 3);
        $expiresAt = now()->addDays($expiresInDays);

        return DB::transaction(function () use ($initiator, $initiatorAccount, $data, $mode, $totalAmount, $expiresAt, $computedShares) {
            /** @var BillSplit $billSplit */
            $billSplit = BillSplit::create([
                'initiator_user_id' => $initiator->id,
                'initiator_account_id' => $initiatorAccount->id,
                'title' => $data['title'],
                'total_amount' => $totalAmount,
                'mode' => $mode,
                'status' => BillSplitStatus::PENDING,
                'merchant_account_id' => $data['merchant_account_id'] ?? null,
                'merchant_name' => $data['merchant_name'] ?? null,
                'note' => $data['note'] ?? null,
                'expires_at' => $expiresAt,
            ]);

            $createdParticipants = [];

            foreach ($computedShares as $share) {
                $userId = (int) $share['user_id'];
                $isInitiator = ($userId === (int) $initiator->id);
                /** @var User $user */
                $user = User::with('account')->findOrFail($userId);

                if (! $user->account) {
                    throw ValidationException::withMessages([
                        'participants' => "User {$user->name} does not have an active wallet.",
                    ]);
                }

                $moneyRequest = null;
                if (! $isInitiator) {
                    $moneyRequest = MoneyRequest::create([
                        'requester_account_id' => $initiatorAccount->id,
                        'payer_account_id' => $user->account->id,
                        'amount' => $share['share_amount'],
                        'status' => RequestStatus::PENDING,
                        'expires_at' => $expiresAt,
                    ]);
                }

                /** @var BillSplitParticipant $participant */
                $participant = BillSplitParticipant::create([
                    'bill_split_id' => $billSplit->id,
                    'user_id' => $user->id,
                    'account_id' => $user->account->id,
                    'share_amount' => $share['share_amount'],
                    'share_value' => $share['share_value'],
                    'is_initiator' => $isInitiator,
                    'status' => $isInitiator ? BillSplitParticipantStatus::ACCEPTED : BillSplitParticipantStatus::PENDING,
                    'money_request_id' => $moneyRequest?->id,
                    'accepted_at' => $isInitiator ? now() : null,
                ]);

                $createdParticipants[] = [
                    'participant' => $participant,
                    'user' => $user,
                    'is_initiator' => $isInitiator,
                ];
            }

            // Post-commit notifications
            DB::afterCommit(function () use ($billSplit, $createdParticipants) {
                foreach ($createdParticipants as $item) {
                    if (! $item['is_initiator']) {
                        $item['user']->notify(new BillSplitInvitationNotification($billSplit, $item['participant']));
                    }
                }
            });

            return $billSplit->load(['participants.user', 'initiatorUser']);
        });
    }

    /**
     * Accept participation in a bill split (places a Hold on participant wallet).
     */
    public function acceptParticipant(BillSplitParticipant $participant): BillSplit
    {
        $billSplit = $participant->relationLoaded('billSplit')
            ? $participant->billSplit
            : $participant->billSplit()->with('initiatorUser')->firstOrFail();

        if ($billSplit->status !== BillSplitStatus::PENDING) {
            throw ValidationException::withMessages([
                'bill_split' => "Cannot accept a bill split that is in '{$billSplit->status->value}' status.",
            ]);
        }

        if ($participant->status === BillSplitParticipantStatus::ACCEPTED) {
            return $billSplit;
        }

        if ($billSplit->expires_at && $billSplit->expires_at->isPast()) {
            $this->failBillSplit($billSplit, 'Bill split invitation expired.');
            throw ValidationException::withMessages([
                'bill_split' => 'This bill split has expired.',
            ]);
        }

        // Place a Hold on the participant's account for their share
        $account = $participant->relationLoaded('account')
            ? $participant->account
            : $participant->account()->firstOrFail();

        if (! $account || $account->available_balance < $participant->share_amount) {
            throw ValidationException::withMessages([
                'balance' => 'Insufficient available balance to accept this bill split share.',
            ]);
        }

        DB::transaction(function () use ($participant, $account, $billSplit) {
            $hold = $this->holdService->createHold(
                account: $account,
                amount: $participant->share_amount,
                reason: "Bill split hold: {$billSplit->title}",
                reference: $billSplit,
            );

            $participant->update([
                'status' => BillSplitParticipantStatus::ACCEPTED,
                'hold_id' => $hold->id,
                'accepted_at' => now(),
            ]);

            $moneyRequest = $participant->relationLoaded('moneyRequest')
                ? $participant->moneyRequest
                : $participant->moneyRequest()->first();

            if ($moneyRequest) {
                $moneyRequest->update([
                    'hold_id' => $hold->id,
                ]);
            }
        });

        // Check if all participants have accepted
        $allAccepted = $billSplit->participants()
            ->where('status', '!=', BillSplitParticipantStatus::ACCEPTED)
            ->count() === 0;

        if ($allAccepted) {
            $this->settleBillSplit($billSplit);
        }

        return $billSplit->fresh(['participants.user', 'initiatorUser']);
    }

    /**
     * Reject participation in a bill split (triggers collective failure and releases any active holds).
     */
    public function rejectParticipant(BillSplitParticipant $participant, ?string $reason = null): BillSplit
    {
        $billSplit = $participant->relationLoaded('billSplit')
            ? $participant->billSplit
            : $participant->billSplit()->with('initiatorUser')->firstOrFail();

        if ($billSplit->status !== BillSplitStatus::PENDING) {
            throw ValidationException::withMessages([
                'bill_split' => "Cannot reject a bill split that is in '{$billSplit->status->value}' status.",
            ]);
        }

        $participant->update([
            'status' => BillSplitParticipantStatus::REJECTED,
        ]);

        $user = $participant->relationLoaded('user') ? $participant->user : $participant->user()->firstOrFail();
        $failReason = 'Declined by '.$user->name.($reason ? ": {$reason}" : '');
        $this->failBillSplit($billSplit, $failReason);

        return $billSplit->fresh(['participants.user', 'initiatorUser']);
    }

    /**
     * Cancel a pending bill split by initiator.
     */
    public function cancelBillSplit(BillSplit $billSplit, User $initiator): BillSplit
    {
        if ((int) $billSplit->initiator_user_id !== (int) $initiator->id) {
            throw ValidationException::withMessages([
                'initiator' => 'Only the initiator can cancel this bill split.',
            ]);
        }

        if ($billSplit->status !== BillSplitStatus::PENDING) {
            throw ValidationException::withMessages([
                'bill_split' => "Cannot cancel a bill split that is in '{$billSplit->status->value}' status.",
            ]);
        }

        $this->failBillSplit($billSplit, 'Cancelled by initiator');

        return $billSplit->fresh(['participants.user', 'initiatorUser']);
    }

    /**
     * Atomic collective settlement when all participants have accepted.
     */
    protected function settleBillSplit(BillSplit $billSplit): void
    {
        $billSplit->loadMissing(['initiatorAccount.user', 'initiatorUser']);
        $initiatorAccount = $billSplit->initiatorAccount;
        /** @var Collection<BillSplitParticipant> $participants */
        $participants = $billSplit->participants()->with(['user', 'account.user', 'hold', 'moneyRequest'])->get();

        DB::transaction(function () use ($billSplit, $initiatorAccount, $participants) {
            foreach ($participants as $participant) {
                if ($participant->is_initiator) {
                    continue;
                }

                // 1. Capture hold
                if ($participant->hold && $participant->hold->status === HoldStatus::ACTIVE) {
                    $this->holdService->captureHold($participant->hold);
                }

                // 2. Transfer from participant -> initiator
                $this->transferService->transfer(
                    fromAccount: $participant->account,
                    toAccount: $initiatorAccount,
                    amount: $participant->share_amount,
                    type: TransactionType::TRANSFER,
                    idempotencyKey: "bill-split:{$billSplit->id}:p:{$participant->id}",
                    initiatedByUserId: $participant->user_id,
                    metadata: [
                        'bill_split_id' => $billSplit->id,
                        'bill_split_title' => $billSplit->title,
                    ],
                );

                // 3. Mark money request approved
                if ($participant->moneyRequest) {
                    $participant->moneyRequest->update([
                        'status' => RequestStatus::APPROVED,
                    ]);
                }
            }

            // 4. If an external merchant was specified, pay the merchant from initiator
            if ($billSplit->merchant_account_id) {
                /** @var Account $merchantAccount */
                $merchantAccount = Account::with('user')->findOrFail($billSplit->merchant_account_id);
                $this->transferService->transfer(
                    fromAccount: $initiatorAccount,
                    toAccount: $merchantAccount,
                    amount: $billSplit->total_amount,
                    type: TransactionType::PAYMENT,
                    idempotencyKey: "bill-split:{$billSplit->id}:merchant-settlement",
                    initiatedByUserId: $billSplit->initiator_user_id,
                    metadata: [
                        'bill_split_id' => $billSplit->id,
                        'merchant_name' => $billSplit->merchant_name,
                    ],
                );
            }

            $billSplit->update([
                'status' => BillSplitStatus::COMPLETED,
                'settled_at' => now(),
            ]);

            // Notify all participants
            DB::afterCommit(function () use ($billSplit, $participants) {
                foreach ($participants as $participant) {
                    $participant->user->notify(new BillSplitCompletedNotification(
                        billSplit: $billSplit,
                        userShareAmount: $participant->share_amount,
                        isInitiator: $participant->is_initiator,
                    ));
                }
            });
        });
    }

    /**
     * Atomic collective failure & release of all pre-placed holds.
     */
    protected function failBillSplit(BillSplit $billSplit, string $reason): void
    {
        $participants = $billSplit->participants()->with(['user', 'account.user', 'hold', 'moneyRequest'])->get();

        DB::transaction(function () use ($billSplit, $participants) {
            $billSplit->update([
                'status' => BillSplitStatus::FAILED,
            ]);

            foreach ($participants as $participant) {
                if ($participant->hold && $participant->hold->status === HoldStatus::ACTIVE) {
                    try {
                        $this->holdService->releaseHold($participant->hold);
                    } catch (Throwable) {
                        // Hold might already be released
                    }
                }

                if ($participant->moneyRequest && $participant->moneyRequest->status === RequestStatus::PENDING) {
                    $participant->moneyRequest->update([
                        'status' => RequestStatus::REJECTED,
                    ]);
                }
            }

            DB::afterCommit(function () use ($billSplit, $participants, $reason) {
                foreach ($participants as $participant) {
                    $participant->user->notify(new BillSplitFailedNotification(
                        billSplit: $billSplit,
                        reason: $reason,
                        hadHoldReleased: (bool) ($participant->hold_id && $participant->status === BillSplitParticipantStatus::ACCEPTED),
                    ));
                }
            });
        });
    }

    /**
     * Calculate share amounts across participants for equal, percentage, and weighted modes.
     *
     * @param  array<array{user_id: int, value?: float}>  $participants
     * @return array<array{user_id: int, share_amount: int, share_value: float}>
     */
    protected function calculateShares(int $totalAmount, BillSplitMode $mode, array $participants, int $initiatorId): array
    {
        $count = count($participants);
        if ($count < 2) {
            throw ValidationException::withMessages([
                'participants' => 'A bill split must have at least 2 participants (including initiator).',
            ]);
        }

        $results = [];
        $runningSum = 0;
        $initiatorIndex = 0;

        match ($mode) {
            BillSplitMode::EQUAL => (function () use ($totalAmount, $participants, $count, &$results, &$runningSum, &$initiatorIndex, $initiatorId) {
                $baseShare = intdiv($totalAmount, $count);

                foreach ($participants as $idx => $p) {
                    $userId = (int) $p['user_id'];
                    if ($userId === $initiatorId) {
                        $initiatorIndex = $idx;
                    }

                    $results[$idx] = [
                        'user_id' => $userId,
                        'share_amount' => $baseShare,
                        'share_value' => 1.0,
                    ];
                    $runningSum += $baseShare;
                }

                // Give rounding remainder to initiator
                $remainder = $totalAmount - $runningSum;
                $results[$initiatorIndex]['share_amount'] += $remainder;
            })(),

            BillSplitMode::PERCENTAGE => (function () use ($totalAmount, $participants, &$results, &$runningSum, &$initiatorIndex, $initiatorId) {
                $percentSum = 0.0;
                foreach ($participants as $p) {
                    $percentSum += (float) ($p['value'] ?? 0);
                }

                if (abs($percentSum - 100.0) > 0.01) {
                    throw ValidationException::withMessages([
                        'participants' => 'Sum of participant percentages must equal 100%. (Current sum: '.$percentSum.'%)',
                    ]);
                }

                foreach ($participants as $idx => $p) {
                    $userId = (int) $p['user_id'];
                    if ($userId === $initiatorId) {
                        $initiatorIndex = $idx;
                    }

                    $pct = (float) ($p['value'] ?? 0);
                    $share = (int) round($totalAmount * ($pct / 100.0));

                    $results[$idx] = [
                        'user_id' => $userId,
                        'share_amount' => $share,
                        'share_value' => $pct,
                    ];
                    $runningSum += $share;
                }

                $remainder = $totalAmount - $runningSum;
                $results[$initiatorIndex]['share_amount'] += $remainder;
            })(),

            BillSplitMode::WEIGHTS => (function () use ($totalAmount, $participants, &$results, &$runningSum, &$initiatorIndex, $initiatorId) {
                $weightSum = 0.0;
                foreach ($participants as $p) {
                    $w = (float) ($p['value'] ?? 1);
                    if ($w <= 0) {
                        throw ValidationException::withMessages([
                            'participants' => 'Participant weights must be positive numbers.',
                        ]);
                    }
                    $weightSum += $w;
                }

                foreach ($participants as $idx => $p) {
                    $userId = (int) $p['user_id'];
                    if ($userId === $initiatorId) {
                        $initiatorIndex = $idx;
                    }

                    $w = (float) ($p['value'] ?? 1);
                    $share = (int) round($totalAmount * ($w / $weightSum));

                    $results[$idx] = [
                        'user_id' => $userId,
                        'share_amount' => $share,
                        'share_value' => $w,
                    ];
                    $runningSum += $share;
                }

                $remainder = $totalAmount - $runningSum;
                $results[$initiatorIndex]['share_amount'] += $remainder;
            })(),
        };

        return $results;
    }
}
