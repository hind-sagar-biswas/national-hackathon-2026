<?php

namespace App\Services\Banking;

use App\Enums\LoanStatus;
use App\Enums\MoneyRequestType;
use App\Enums\RequestStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Loan;
use App\Models\MoneyRequest;
use App\Models\Transaction;
use App\Notifications\Banking\LoanDisbursedNotification;
use App\Notifications\Banking\MoneyRequestApprovedNotification;
use App\Notifications\Banking\MoneyRequestExpiredNotification;
use App\Notifications\Banking\MoneyRequestReceivedNotification;
use App\Notifications\Banking\MoneyRequestRejectedNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MoneyRequestService
{
    public function __construct(
        protected TransferService $transferService,
        protected HoldService $holdService,
    ) {}

    /**
     * Create a money request with an optional pre-hold on the payer.
     */
    public function createRequest(
        Account $requesterAccount,
        Account $payerAccount,
        int $amount,
        MoneyRequestType $type = MoneyRequestType::STANDARD,
        ?CarbonInterface $expiresAt = null,
        ?CarbonInterface $dueAt = null,
        ?string $note = null,
        bool $preHold = false,
    ): MoneyRequest {
        if ($amount <= 0) {
            throw new RuntimeException('Requested amount must be greater than zero.');
        }

        if ($requesterAccount->id === $payerAccount->id) {
            throw new RuntimeException('Requester and payer accounts cannot be the same.');
        }

        $expiresAt = $expiresAt ?: now()->addDays(3);

        return DB::transaction(function () use ($requesterAccount, $payerAccount, $amount, $type, $expiresAt, $dueAt, $note, $preHold) {
            $moneyRequest = MoneyRequest::create([
                'requester_account_id' => $requesterAccount->id,
                'payer_account_id' => $payerAccount->id,
                'amount' => $amount,
                'type' => $type,
                'status' => RequestStatus::PENDING,
                'expires_at' => $expiresAt,
                'due_at' => $dueAt,
                'note' => $note,
            ]);

            if ($preHold) {
                $hold = $this->holdService->createHold(
                    $payerAccount,
                    $amount,
                    "Money request #{$moneyRequest->id} pre-reservation",
                    $moneyRequest
                );

                $moneyRequest->update(['hold_id' => $hold->id]);
            }

            DB::afterCommit(function () use ($moneyRequest, $payerAccount, $requesterAccount) {
                $payerAccount->loadMissing('user');
                $requesterAccount->loadMissing('user');
                $payerUser = $payerAccount->user;
                if ($payerUser) {
                    $requesterName = $requesterAccount->user ? $requesterAccount->user->name : 'Someone';
                    $payerUser->notify(new MoneyRequestReceivedNotification($moneyRequest, $requesterName));
                }
            });

            return $moneyRequest;
        });
    }

    /**
     * Approve and settle a money request (creates Loan if type is loan).
     */
    public function approve(MoneyRequest $moneyRequest, ?string $idempotencyKey = null): Transaction
    {
        if ($moneyRequest->status !== RequestStatus::PENDING) {
            throw new RuntimeException("Money request cannot be approved in '{$moneyRequest->status->value}' status.");
        }

        if ($moneyRequest->expires_at && $moneyRequest->expires_at->isPast()) {
            $this->expire($moneyRequest);
            throw new RuntimeException('This money request has expired.');
        }

        $idempotencyKey = $idempotencyKey ?: "req_settle_{$moneyRequest->id}";
        $isLoan = ($moneyRequest->type === MoneyRequestType::LOAN);
        $transactionType = $isLoan ? TransactionType::LOAN_DISBURSEMENT : TransactionType::REQUEST_SETTLEMENT;

        return DB::transaction(function () use ($moneyRequest, $idempotencyKey, $transactionType, $isLoan) {
            /** @var Transaction $transaction */
            if ($moneyRequest->hold_id && $moneyRequest->hold) {
                $transaction = $this->holdService->captureHold($moneyRequest->hold, function (Account $payerAccount) use ($moneyRequest, $idempotencyKey, $transactionType) {
                    return $this->transferService->transfer(
                        fromAccount: $payerAccount,
                        toAccount: $moneyRequest->requesterAccount,
                        amount: $moneyRequest->amount,
                        type: $transactionType,
                        idempotencyKey: $idempotencyKey,
                        initiatedByUserId: $payerAccount->user_id,
                        metadata: ['money_request_id' => $moneyRequest->id],
                    );
                });
            } else {
                $transaction = $this->transferService->transfer(
                    fromAccount: $moneyRequest->payerAccount,
                    toAccount: $moneyRequest->requesterAccount,
                    amount: $moneyRequest->amount,
                    type: $transactionType,
                    idempotencyKey: $idempotencyKey,
                    initiatedByUserId: $moneyRequest->payerAccount->user_id,
                    metadata: ['money_request_id' => $moneyRequest->id],
                );
            }

            $loan = null;
            if ($isLoan) {
                /** @var Loan $loan */
                $loan = Loan::create([
                    'lender_user_id' => $moneyRequest->payerAccount->user_id,
                    'borrower_user_id' => $moneyRequest->requesterAccount->user_id,
                    'principal_amount' => $moneyRequest->amount,
                    'outstanding_amount' => $moneyRequest->amount,
                    'status' => LoanStatus::ACTIVE,
                    'disbursement_txn_id' => $transaction->id,
                    'money_request_id' => $moneyRequest->id,
                    'due_at' => $moneyRequest->due_at,
                    'note' => $moneyRequest->note,
                ]);
            }

            $moneyRequest->update([
                'status' => RequestStatus::APPROVED,
                'transaction_id' => $transaction->id,
                'loan_id' => $loan?->id,
            ]);

            DB::afterCommit(function () use ($moneyRequest, $loan) {
                $moneyRequest->loadMissing(['requesterAccount.user', 'payerAccount.user']);
                $requesterUser = $moneyRequest->requesterAccount->user;
                $payerUser = $moneyRequest->payerAccount->user;
                $payerName = $payerUser ? $payerUser->name : 'Payer';

                if ($requesterUser) {
                    $requesterUser->notify(new MoneyRequestApprovedNotification($moneyRequest, $payerName));

                    if ($loan) {
                        $requesterUser->notify(new LoanDisbursedNotification($loan, $payerName));
                    }
                }
            });

            return $transaction;
        });
    }

    /**
     * Reject a money request and release any active holds.
     */
    public function reject(MoneyRequest $moneyRequest): void
    {
        if ($moneyRequest->status !== RequestStatus::PENDING) {
            throw new RuntimeException("Money request cannot be rejected in '{$moneyRequest->status->value}' status.");
        }

        DB::transaction(function () use ($moneyRequest) {
            if ($moneyRequest->hold_id && $moneyRequest->hold) {
                $this->holdService->releaseHold($moneyRequest->hold);
            }

            $moneyRequest->update(['status' => RequestStatus::REJECTED]);

            DB::afterCommit(function () use ($moneyRequest) {
                $moneyRequest->loadMissing(['requesterAccount.user', 'payerAccount.user']);
                $requesterUser = $moneyRequest->requesterAccount->user;
                if ($requesterUser) {
                    $payerName = $moneyRequest->payerAccount->user ? $moneyRequest->payerAccount->user->name : 'Payer';
                    $requesterUser->notify(new MoneyRequestRejectedNotification($moneyRequest, $payerName));
                }
            });
        });
    }

    /**
     * Expire an unapproved money request.
     */
    public function expire(MoneyRequest $moneyRequest): void
    {
        if ($moneyRequest->status !== RequestStatus::PENDING) {
            return;
        }

        DB::transaction(function () use ($moneyRequest) {
            if ($moneyRequest->hold_id && $moneyRequest->hold) {
                $this->holdService->releaseHold($moneyRequest->hold);
            }

            $moneyRequest->update(['status' => RequestStatus::EXPIRED]);

            DB::afterCommit(function () use ($moneyRequest) {
                $requesterUser = $moneyRequest->requesterAccount->user;
                $payerUser = $moneyRequest->payerAccount->user;

                if ($requesterUser) {
                    $requesterUser->notify(new MoneyRequestExpiredNotification($moneyRequest, isRequester: true));
                }
                if ($payerUser) {
                    $payerUser->notify(new MoneyRequestExpiredNotification($moneyRequest, isRequester: false));
                }
            });
        });
    }
}
