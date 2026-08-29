<?php

namespace App\Services\Banking;

use App\Enums\AccountOwner;
use App\Enums\TransactionDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\OperationEvent;
use App\Models\Transaction;
use App\Notifications\Banking\MoneyReceivedNotification;
use App\Notifications\Banking\MoneySentNotification;
use App\Notifications\Banking\SuspiciousActivityAlertNotification;
use App\Notifications\Banking\TransactionHeldNotification;
use App\Services\Auth\OtpService;
use App\Services\Risk\DTOs\RiskContextDTO;
use App\Services\Risk\Exceptions\RiskChallengeRequiredException;
use App\Services\Risk\Exceptions\TransactionHeldForReviewException;
use App\Services\Risk\RiskEvaluationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TransferService
{
    public function __construct(
        protected ?RiskEvaluationService $riskEvaluationService = null,
        protected ?HoldService $holdService = null,
        protected ?OtpService $otpService = null,
    ) {}

    /**
     * Universal double-entry transfer engine with integrated risk evaluation.
     */
    public function executeWithRiskCheck(
        Account $fromAccount,
        Account $toAccount,
        int $amount,
        TransactionType $type,
        string $idempotencyKey,
        ?int $initiatedByUserId = null,
        int $feeAmount = 0,
        array $metadata = [],
        ?string $reference = null,
        ?string $otpCode = null,
    ): Transaction {
        $riskService = $this->riskEvaluationService ?? app(RiskEvaluationService::class);
        $holdService = $this->holdService ?? app(HoldService::class);
        $otpService = $this->otpService ?? app(OtpService::class);

        $context = RiskContextDTO::fromRequest($fromAccount, $toAccount, $amount, $metadata);
        $assessment = $riskService->evaluate($context);

        // 1. High Risk -> Freeze funds in a Hold for compliance review
        if ($assessment->shouldHold()) {
            $totalHoldAmount = $amount + $feeAmount;
            $hold = $holdService->createHold(
                account: $fromAccount,
                amount: $totalHoldAmount,
                reason: 'Automated security hold: elevated risk score',
            );

            OperationEvent::firstOrCreate(
                ['operation_key' => $idempotencyKey, 'status' => TransactionStatus::HELD],
                [
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $amount,
                    'metadata' => array_merge($metadata, [
                        'risk' => $assessment->toArray(),
                        'hold_id' => $hold->id,
                    ]),
                ]
            );

            if ($fromAccount->user) {
                $dummyTxn = new Transaction([
                    'reference' => $reference ?: 'HOLD-'.strtoupper(Str::random(8)),
                    'id' => 'HELD-'.$hold->id,
                ]);
                $fromAccount->user->notify(new TransactionHeldNotification($dummyTxn, $amount, 'High security risk score'));
            }

            throw new TransactionHeldForReviewException($assessment, $hold);
        }

        // 2. Mod-High Risk -> Step-up OTP challenge required
        if ($assessment->requiresChallenge() && $fromAccount->user) {
            if ($otpCode) {
                $isValid = $otpService->verify($fromAccount->user, $otpCode, 'transfer');
                if (! $isValid) {
                    throw ValidationException::withMessages([
                        'otp_code' => 'Invalid or expired OTP verification code.',
                    ]);
                }
            } else {
                // Generate and dispatch OTP challenge to user
                $otpService->generateAndSend($fromAccount->user, 'transfer');

                throw new RiskChallengeRequiredException(
                    assessment: $assessment,
                    message: 'Security verification required. A 6-digit OTP has been sent to your registered contact.'
                );
            }
        }

        // 3. Moderate Risk -> Proceed normally + async security notice to user
        if ($assessment->shouldNotify()) {
            DB::afterCommit(function () use ($fromAccount, $amount) {
                if ($fromAccount->user) {
                    $formatted = formatPaisa($amount);
                    $fromAccount->user->notify(new SuspiciousActivityAlertNotification(
                        actionDescription: "Transfer of {$formatted} BDT",
                        ip: request()?->ip(),
                    ));
                }
            });
        }

        return $this->transfer(
            fromAccount: $fromAccount,
            toAccount: $toAccount,
            amount: $amount,
            type: $type,
            idempotencyKey: $idempotencyKey,
            initiatedByUserId: $initiatedByUserId,
            feeAmount: $feeAmount,
            metadata: array_merge($metadata, ['risk' => $assessment->toArray()]),
            reference: $reference,
        );
    }

    /**
     * Universal double-entry transfer engine.
     *
     * @param  Account  $fromAccount  The sender/debit account
     * @param  Account  $toAccount  The receiver/credit account
     * @param  int  $amount  The transfer amount in smallest currency units
     * @param  TransactionType  $type  The domain transaction type
     * @param  string  $idempotencyKey  Unique client or operation idempotency key
     * @param  int|null  $initiatedByUserId  The user initiating this action
     * @param  int  $feeAmount  Optional fee taken by platform
     * @param  array  $metadata  Additional contextual attributes
     * @param  string|null  $reference  Custom transaction reference
     */
    public function transfer(
        Account $fromAccount,
        Account $toAccount,
        int $amount,
        TransactionType $type,
        string $idempotencyKey,
        ?int $initiatedByUserId = null,
        int $feeAmount = 0,
        array $metadata = [],
        ?string $reference = null,
        bool $dispatchNotifications = true,
    ): Transaction {
        if ($amount <= 0) {
            throw new RuntimeException('Transfer amount must be greater than zero.');
        }

        if ($feeAmount < 0) {
            throw new RuntimeException('Fee amount cannot be negative.');
        }

        if ($fromAccount->id === $toAccount->id) {
            throw new RuntimeException('Source and destination accounts cannot be the same.');
        }

        // Idempotency short-circuit
        $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        // 1. Log Initiated Event
        OperationEvent::firstOrCreate(
            ['operation_key' => $idempotencyKey, 'status' => TransactionStatus::INITIATED],
            [
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'metadata' => $metadata,
            ]
        );

        $totalDebitAmount = $amount + $feeAmount;
        $reference = $reference ?: 'TRX-'.strtoupper(Str::random(10));

        try {
            return DB::transaction(function () use (
                $fromAccount,
                $toAccount,
                $amount,
                $feeAmount,
                $totalDebitAmount,
                $type,
                $idempotencyKey,
                $initiatedByUserId,
                $metadata,
                $reference,
                $dispatchNotifications
            ) {
                // 2. Deadlock-free account locking using ordered IDs
                $accountIds = array_unique(array_filter([$fromAccount->id, $toAccount->id]));
                sort($accountIds);

                $lockedAccounts = Account::whereIn('id', $accountIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                /** @var Account $lockedSender */
                $lockedSender = $lockedAccounts->get($fromAccount->id);
                /** @var Account $lockedReceiver */
                $lockedReceiver = $lockedAccounts->get($toAccount->id);

                if (! $lockedSender || ! $lockedReceiver) {
                    throw new RuntimeException('Failed to lock accounts for transaction.');
                }

                // Balance check (liability/user accounts cannot overdraw available balance)
                if ($lockedSender->owner_type === AccountOwner::USER && $lockedSender->available_balance < $totalDebitAmount) {
                    throw new RuntimeException("Insufficient available balance. Required: {$totalDebitAmount}, Available: {$lockedSender->available_balance}");
                }

                // 3. Balance Adjustments
                $lockedSender->decrement('available_balance', $totalDebitAmount);
                $lockedSender->decrement('cleared_balance', $totalDebitAmount);
                $lockedSender->refresh();

                $lockedReceiver->increment('available_balance', $amount);
                $lockedReceiver->increment('cleared_balance', $amount);
                $lockedReceiver->refresh();

                /** @var Account|null $feeAccount */
                $feeAccount = null;
                if ($feeAmount > 0) {
                    $feeAccount = Account::where('slug', 'fee_income')->lockForUpdate()->first();
                    if ($feeAccount) {
                        $feeAccount->increment('available_balance', $feeAmount);
                        $feeAccount->increment('cleared_balance', $feeAmount);
                        $feeAccount->refresh();
                    }
                }

                // 4. Create Transaction Header
                $transaction = Transaction::create([
                    'reference' => $reference,
                    'type' => $type,
                    'idempotency_key' => $idempotencyKey,
                    'initiated_by' => $initiatedByUserId,
                    'metadata' => array_merge($metadata, [
                        'amount' => $amount,
                        'fee' => $feeAmount,
                        'total_debit' => $totalDebitAmount,
                    ]),
                ]);

                // 5. Insert Balanced Ledger Entries
                // Leg 1: Debit Sender
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $lockedSender->id,
                    'direction' => TransactionDirection::DEBIT,
                    'amount' => $totalDebitAmount,
                    'balance_after' => $lockedSender->cleared_balance,
                ]);

                // Leg 2: Credit Receiver
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $lockedReceiver->id,
                    'direction' => TransactionDirection::CREDIT,
                    'amount' => $amount,
                    'balance_after' => $lockedReceiver->cleared_balance,
                ]);

                // Leg 3: Credit Fee Income (if fee applies)
                if ($feeAmount > 0 && $feeAccount) {
                    LedgerEntry::create([
                        'transaction_id' => $transaction->id,
                        'account_id' => $feeAccount->id,
                        'direction' => TransactionDirection::CREDIT,
                        'amount' => $feeAmount,
                        'balance_after' => $feeAccount->cleared_balance,
                    ]);
                }

                // 6. Log Applied Event
                OperationEvent::firstOrCreate(
                    ['operation_key' => $idempotencyKey, 'status' => TransactionStatus::COMPLETED],
                    [
                        'from_account_id' => $lockedSender->id,
                        'to_account_id' => $lockedReceiver->id,
                        'amount' => $amount,
                        'metadata' => array_merge($metadata, ['transaction_id' => $transaction->id]),
                    ]
                );

                // 7. Post-Commit Notifications
                if ($dispatchNotifications) {
                    DB::afterCommit(function () use ($lockedSender, $lockedReceiver, $transaction, $amount, $feeAmount) {
                        $senderUser = $lockedSender->user;
                        $receiverUser = $lockedReceiver->user;

                        if ($senderUser) {
                            $receiverName = $receiverUser ? $receiverUser->name : 'System';
                            $senderUser->notify(new MoneySentNotification($transaction, $amount, $receiverName, $feeAmount));
                        }

                        if ($receiverUser) {
                            $senderName = $senderUser ? $senderUser->name : 'System';
                            $receiverUser->notify(new MoneyReceivedNotification($transaction, $amount, $senderName));
                        }
                    });
                }

                return $transaction;
            });
        } catch (Throwable $e) {
            // Log Failure Event
            OperationEvent::firstOrCreate(
                ['operation_key' => $idempotencyKey, 'status' => TransactionStatus::FAILED],
                [
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $amount,
                    'metadata' => array_merge($metadata, ['error' => $e->getMessage()]),
                ]
            );

            throw $e;
        }
    }
}
