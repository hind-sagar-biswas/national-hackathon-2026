<?php

namespace App\Services\Banking;

use App\Enums\LoanStatus;
use App\Enums\MoneyRequestType;
use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\MoneyRequest;
use App\Models\User;
use App\Notifications\Banking\LoanDisbursedNotification;
use App\Notifications\Banking\LoanRepaymentReceivedNotification;
use App\Notifications\Banking\LoanSettledNotification;
use App\Notifications\Banking\LoanWaivedNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service class managing the peer-to-peer micro-loan lifecycle, disbursements, repayments, and debt waivers.
 */
class LoanService
{
    /**
     * Create a new LoanService instance.
     *
     * @param  TransferService  $transferService  The core double-entry transfer engine
     * @param  MoneyRequestService  $moneyRequestService  Service managing direct money and loan requests
     */
    public function __construct(
        protected TransferService $transferService,
        protected MoneyRequestService $moneyRequestService,
    ) {}

    /**
     * Request a peer-to-peer loan from a prospective lender.
     *
     * @param  User  $borrower  The user requesting the loan funds
     * @param  User  $lender  The prospective lender being asked for funds
     * @param  int  $principalAmount  Principal loan amount in smallest currency units (paisa/cents)
     * @param  CarbonInterface|null  $dueAt  Agreed loan repayment due date
     * @param  string|null  $note  Optional loan purpose or contract terms
     * @param  CarbonInterface|null  $expiresAt  Expiration date of the loan request proposal
     * @return MoneyRequest The generated loan-type money request
     *
     * @throws RuntimeException If amount is non-positive, users are identical, or wallets are missing
     */
    public function requestLoan(
        User $borrower,
        User $lender,
        int $principalAmount,
        ?CarbonInterface $dueAt = null,
        ?string $note = null,
        ?CarbonInterface $expiresAt = null,
    ): MoneyRequest {
        if ($principalAmount <= 0) {
            throw new RuntimeException('Principal amount must be greater than zero.');
        }

        if ($borrower->id === $lender->id) {
            throw new RuntimeException('Borrower and lender cannot be the same user.');
        }

        $borrowerAccount = $borrower->account;
        $lenderAccount = $lender->account;

        if (! $borrowerAccount || ! $lenderAccount) {
            throw new RuntimeException('Both borrower and lender must have active wallets.');
        }

        return $this->moneyRequestService->createRequest(
            requesterAccount: $borrowerAccount,
            payerAccount: $lenderAccount,
            amount: $principalAmount,
            type: MoneyRequestType::LOAN,
            expiresAt: $expiresAt,
            dueAt: $dueAt,
            note: $note,
        );
    }

    /**
     * Disburse a peer-to-peer loan from lender to borrower directly.
     *
     * @param  User  $lender  The user lending the capital
     * @param  User  $borrower  The recipient borrower receiving the funds
     * @param  int  $principalAmount  Principal loan amount in smallest currency units (paisa/cents)
     * @param  CarbonInterface|null  $dueAt  Repayment due date
     * @param  string|null  $note  Optional loan description
     * @param  string|null  $idempotencyKey  Unique operation idempotency key
     * @return Loan The newly established active Loan record
     *
     * @throws RuntimeException If amount is invalid, users are identical, or accounts are missing
     */
    public function disburse(
        User $lender,
        User $borrower,
        int $principalAmount,
        ?CarbonInterface $dueAt = null,
        ?string $note = null,
        ?string $idempotencyKey = null,
    ): Loan {
        if ($principalAmount <= 0) {
            throw new RuntimeException('Principal amount must be greater than zero.');
        }

        if ($lender->id === $borrower->id) {
            throw new RuntimeException('Lender and borrower cannot be the same user.');
        }

        $lenderAccount = $lender->account;
        $borrowerAccount = $borrower->account;

        if (! $lenderAccount || ! $borrowerAccount) {
            throw new RuntimeException('Both lender and borrower must have active wallets.');
        }

        $idempotencyKey = $idempotencyKey ?: "loan_disb_{$lender->id}_{$borrower->id}_".Str::random(8);

        return DB::transaction(function () use (
            $lender,
            $borrower,
            $lenderAccount,
            $borrowerAccount,
            $principalAmount,
            $dueAt,
            $note,
            $idempotencyKey
        ) {
            $transaction = $this->transferService->transfer(
                fromAccount: $lenderAccount,
                toAccount: $borrowerAccount,
                amount: $principalAmount,
                type: TransactionType::LOAN_DISBURSEMENT,
                idempotencyKey: $idempotencyKey,
                initiatedByUserId: $lender->id,
                metadata: ['loan_action' => 'disbursement'],
            );

            $loan = Loan::create([
                'lender_user_id' => $lender->id,
                'borrower_user_id' => $borrower->id,
                'principal_amount' => $principalAmount,
                'outstanding_amount' => $principalAmount,
                'status' => LoanStatus::ACTIVE,
                'disbursement_txn_id' => $transaction->id,
                'due_at' => $dueAt,
                'note' => $note,
            ]);

            DB::afterCommit(function () use ($loan, $borrower, $lender) {
                $borrower->notify(new LoanDisbursedNotification($loan, $lender->name));
            });

            return $loan;
        });
    }

    /**
     * Record a partial or full loan repayment from borrower to lender.
     *
     * @param  Loan  $loan  The active loan entity to repay
     * @param  int  $amount  The repayment amount in smallest currency units (paisa/cents)
     * @param  string|null  $idempotencyKey  Unique operation idempotency key
     * @return LoanRepayment The newly created loan repayment record
     *
     * @throws RuntimeException If amount is invalid, loan is not active, or amount exceeds debt
     */
    public function repay(
        Loan $loan,
        int $amount,
        ?string $idempotencyKey = null,
    ): LoanRepayment {
        if ($amount <= 0) {
            throw new RuntimeException('Repayment amount must be greater than zero.');
        }

        if (! in_array($loan->status, [LoanStatus::ACTIVE, LoanStatus::PARTIAL])) {
            throw new RuntimeException("Loan cannot be repaid in '{$loan->status->value}' status.");
        }

        if ($amount > $loan->outstanding_amount) {
            $amtFormatted = formatPaisa($amount);
            $outstandingFormatted = formatPaisa($loan->outstanding_amount);
            throw new RuntimeException("Repayment amount ({$amtFormatted} BDT) exceeds outstanding loan balance ({$outstandingFormatted} BDT).");
        }

        $borrowerAccount = $loan->borrower->account;
        $lenderAccount = $loan->lender->account;

        if (! $borrowerAccount || ! $lenderAccount) {
            throw new RuntimeException('Borrower and lender wallets must be active.');
        }

        $idempotencyKey = $idempotencyKey ?: "loan_repay_{$loan->id}_".Str::random(8);

        return DB::transaction(function () use ($loan, $borrowerAccount, $lenderAccount, $amount, $idempotencyKey) {
            /** @var Loan $lockedLoan */
            $lockedLoan = Loan::where('id', $loan->id)->lockForUpdate()->firstOrFail();

            $transaction = $this->transferService->transfer(
                fromAccount: $borrowerAccount,
                toAccount: $lenderAccount,
                amount: $amount,
                type: TransactionType::LOAN_REPAYMENT,
                idempotencyKey: $idempotencyKey,
                initiatedByUserId: $loan->borrower_user_id,
                metadata: ['loan_id' => $lockedLoan->id, 'loan_action' => 'repayment'],
            );

            $repayment = LoanRepayment::create([
                'loan_id' => $lockedLoan->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
            ]);

            $newOutstanding = max(0, $lockedLoan->outstanding_amount - $amount);
            $newStatus = $newOutstanding === 0 ? LoanStatus::SETTLED : LoanStatus::PARTIAL;

            $lockedLoan->update([
                'outstanding_amount' => $newOutstanding,
                'status' => $newStatus,
            ]);

            DB::afterCommit(function () use ($lockedLoan, $amount, $newStatus) {
                $lender = $lockedLoan->lender;
                $borrower = $lockedLoan->borrower;

                if ($lender) {
                    $lender->notify(new LoanRepaymentReceivedNotification($lockedLoan, $amount, $borrower->name));
                }

                if ($newStatus === LoanStatus::SETTLED) {
                    if ($borrower) {
                        $borrower->notify(new LoanSettledNotification($lockedLoan, $lender->name, isLender: false));
                    }
                    if ($lender) {
                        $lender->notify(new LoanSettledNotification($lockedLoan, $borrower->name, isLender: true));
                    }
                }
            });

            return $repayment;
        });
    }

    /**
     * Forgive / waive an outstanding loan balance without moving funds.
     *
     * @param  Loan  $loan  The loan entity to be forgiven
     * @param  User  $lender  The lender authorizing the waiver
     *
     * @throws RuntimeException If user is not the lender or loan cannot be waived
     */
    public function waive(Loan $loan, User $lender): void
    {
        if ($loan->lender_user_id !== $lender->id) {
            throw new RuntimeException('Only the lender can waive this loan.');
        }

        if (! in_array($loan->status, [LoanStatus::ACTIVE, LoanStatus::PARTIAL])) {
            throw new RuntimeException("Loan cannot be waived in '{$loan->status->value}' status.");
        }

        DB::transaction(function () use ($loan, $lender) {
            $loan->update([
                'status' => LoanStatus::WAIVED,
                'outstanding_amount' => 0,
            ]);

            DB::afterCommit(function () use ($loan, $lender) {
                $borrower = $loan->borrower;
                if ($borrower) {
                    $borrower->notify(new LoanWaivedNotification($loan, $lender->name));
                }
            });
        });
    }
}
