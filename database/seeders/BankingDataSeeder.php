<?php

namespace Database\Seeders;

use App\Enums\BillSplitMode;
use App\Enums\DepositProvider;
use App\Enums\MoneyRequestType;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\Banking\BillSplitService;
use App\Services\Banking\DepositService;
use App\Services\Banking\HoldService;
use App\Services\Banking\LoanService;
use App\Services\Banking\MoneyRequestService;
use App\Services\Banking\ReconciliationService;
use App\Services\Banking\TransferService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BankingDataSeeder extends Seeder
{
    public function __construct(
        protected DepositService $depositService,
        protected TransferService $transferService,
        protected HoldService $holdService,
        protected MoneyRequestService $moneyRequestService,
        protected LoanService $loanService,
        protected BillSplitService $billSplitService,
        protected ReconciliationService $reconciliationService,
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load seeded users & their accounts
        /** @var User $user1 */
        $user1 = User::with('account')->where('email', 'user@test.com')->firstOrFail();
        /** @var User $user2 */
        $user2 = User::with('account')->where('email', 'user2@test.com')->firstOrFail();
        /** @var User $user3 */
        $user3 = User::with('account')->where('email', 'user3@test.com')->firstOrFail();
        /** @var User $user4 */
        $user4 = User::with('account')->where('email', 'user4@test.com')->firstOrFail();
        /** @var User $user5 */
        $user5 = User::with('account')->where('email', 'user5@test.com')->firstOrFail();
        /** @var User $merchant */
        $merchant = User::with('account')->where('email', 'merchant@test.com')->firstOrFail();

        $merchantAccount = $merchant->account;

        // ---------------------------------------------------------------------
        // 1. DEPOSITS (Confirmed, Pending, Rejected)
        // ---------------------------------------------------------------------
        // Confirmed bKash Deposit for User 1 (5,000 BDT)
        $dep1 = $this->depositService->initiate(
            user: $user1,
            provider: DepositProvider::BKASH,
            providerRef: 'BKASH-TXN-984214',
            amount: toPaisa(5000),
        );
        $this->depositService->confirm($dep1, 'seed_dep_confirm_1');

        // Confirmed Nagad Deposit for User 2 (10,000 BDT)
        $dep2 = $this->depositService->initiate(
            user: $user2,
            provider: DepositProvider::NAGAD,
            providerRef: 'NGD-TXN-773190',
            amount: toPaisa(10000),
        );
        $this->depositService->confirm($dep2, 'seed_dep_confirm_2');

        // Confirmed Bank Transfer Deposit for User 4 (15,000 BDT)
        $dep3 = $this->depositService->initiate(
            user: $user4,
            provider: DepositProvider::BANK,
            providerRef: 'EBL-NPSB-552109',
            amount: toPaisa(15000),
        );
        $this->depositService->confirm($dep3, 'seed_dep_confirm_3');

        // Rejected Deposit for User 5 (1,000 BDT)
        $dep5 = $this->depositService->initiate(
            user: $user5,
            provider: DepositProvider::BKASH,
            providerRef: 'BKASH-INVALID-001',
            amount: toPaisa(1000),
        );
        $this->depositService->reject($dep5, 'Invalid receipt reference ID or unmatched mobile number.');

        // ---------------------------------------------------------------------
        // 2. DIRECT P2P TRANSFERS & MERCHANT PAYMENTS
        // ---------------------------------------------------------------------
        // User 1 -> User 2 (1,200 BDT)
        $this->transferService->transfer(
            fromAccount: $user1->account,
            toAccount: $user2->account,
            amount: toPaisa(1200),
            type: TransactionType::TRANSFER,
            idempotencyKey: 'seed_transfer_1',
            initiatedByUserId: $user1->id,
            metadata: ['note' => 'Dinner last night at Dhanmondi'],
        );

        // User 2 -> User 3 (500 BDT)
        $this->transferService->transfer(
            fromAccount: $user2->account,
            toAccount: $user3->account,
            amount: toPaisa(500),
            type: TransactionType::TRANSFER,
            idempotencyKey: 'seed_transfer_2',
            initiatedByUserId: $user2->id,
            metadata: ['note' => 'Algorithms Textbook purchase'],
        );

        // User 4 -> User 1 (3,000 BDT)
        $this->transferService->transfer(
            fromAccount: $user4->account,
            toAccount: $user1->account,
            amount: toPaisa(3000),
            type: TransactionType::TRANSFER,
            idempotencyKey: 'seed_transfer_3',
            initiatedByUserId: $user4->id,
            metadata: ['note' => 'Project collaboration payout'],
        );

        // User 1 -> Merchant (850 BDT)
        if ($merchantAccount) {
            $this->transferService->transfer(
                fromAccount: $user1->account,
                toAccount: $merchantAccount,
                amount: toPaisa(850),
                type: TransactionType::PAYMENT,
                idempotencyKey: 'seed_payment_1',
                initiatedByUserId: $user1->id,
                metadata: ['note' => 'Cappuccino and Blueberry Muffin'],
            );
        }

        // ---------------------------------------------------------------------
        // 3. BALANCE HOLDS
        // ---------------------------------------------------------------------
        // Active Compliance Hold on User 5 (1,500 BDT)
        $this->holdService->createHold(
            account: $user5->account,
            amount: toPaisa(1500),
            reason: 'Temporary compliance verification hold',
        );

        // ---------------------------------------------------------------------
        // 4. MONEY REQUESTS
        // ---------------------------------------------------------------------
        // Pending Standard Request: User 2 requests 750 BDT from User 1
        $this->moneyRequestService->createRequest(
            requesterAccount: $user2->account,
            payerAccount: $user1->account,
            amount: toPaisa(750),
            type: MoneyRequestType::STANDARD,
            expiresAt: now()->addDays(3),
            note: 'Shared ride fare to workplace',
        );

        // Approved Request: User 3 requested 1,000 BDT from User 4 (Approved)
        $req2 = $this->moneyRequestService->createRequest(
            requesterAccount: $user3->account,
            payerAccount: $user4->account,
            amount: toPaisa(1000),
            type: MoneyRequestType::STANDARD,
            expiresAt: now()->addDays(3),
            note: 'Conference ticket reimbursement',
        );
        $this->moneyRequestService->approve($req2, 'seed_mr_approve_1');

        // Pre-Hold Request: User 1 requests 500 BDT from User 2 with pre-hold
        $this->moneyRequestService->createRequest(
            requesterAccount: $user1->account,
            payerAccount: $user2->account,
            amount: toPaisa(500),
            type: MoneyRequestType::STANDARD,
            expiresAt: now()->addDays(2),
            note: 'Team coffee contribution',
            preHold: true,
        );

        // Declined Request: User 5 requested 2,000 BDT from User 1 (Declined)
        $req4 = $this->moneyRequestService->createRequest(
            requesterAccount: $user5->account,
            payerAccount: $user1->account,
            amount: toPaisa(2000),
            type: MoneyRequestType::STANDARD,
            expiresAt: now()->addDays(1),
            note: 'Equipment rental reimbursement',
        );
        $this->moneyRequestService->reject($req4, 'Duplicate request; already settled in cash.');

        // ---------------------------------------------------------------------
        // 5. PEER-TO-PEER LOANS
        // ---------------------------------------------------------------------
        // Active Loan: User 4 lends 5,000 BDT to User 3 (Due in 30 days)
        $this->loanService->disburse(
            lender: $user4,
            borrower: $user3,
            principalAmount: toPaisa(5000),
            dueAt: Carbon::now()->addDays(30),
            note: 'Semester tuition emergency assistance',
            idempotencyKey: 'seed_loan_1',
        );

        // Partially Repaid Loan: User 1 lends 4,000 BDT to User 2. User 2 repays 1,500 BDT.
        $loan2 = $this->loanService->disburse(
            lender: $user1,
            borrower: $user2,
            principalAmount: toPaisa(4000),
            dueAt: Carbon::now()->addDays(14),
            note: 'Laptop repair cost',
            idempotencyKey: 'seed_loan_2',
        );
        $this->loanService->repay(
            loan: $loan2,
            amount: toPaisa(1500),
            idempotencyKey: 'seed_loan_repay_1',
        );

        // Fully Settled Loan: User 2 lends 2,000 BDT to User 3, fully repaid in 2 installments
        $loan3 = $this->loanService->disburse(
            lender: $user2,
            borrower: $user3,
            principalAmount: toPaisa(2000),
            dueAt: Carbon::now()->addDays(7),
            note: 'Grocery cash float',
            idempotencyKey: 'seed_loan_3',
        );
        $this->loanService->repay(
            loan: $loan3,
            amount: toPaisa(1000),
            idempotencyKey: 'seed_loan_repay_2a',
        );
        $this->loanService->repay(
            loan: $loan3,
            amount: toPaisa(1000),
            idempotencyKey: 'seed_loan_repay_2b',
        );

        // Waived Loan: User 1 lends 1,000 BDT to User 5, and remaining balance is waived
        $loan4 = $this->loanService->disburse(
            lender: $user1,
            borrower: $user5,
            principalAmount: toPaisa(1000),
            dueAt: Carbon::now()->addDays(10),
            note: 'Medical prescription assistance',
            idempotencyKey: 'seed_loan_4',
        );
        $this->loanService->waive($loan4, $user1);

        // ---------------------------------------------------------------------
        // 6. MULTI-PARTICIPANT BILL SPLITS
        // ---------------------------------------------------------------------
        // A. Completed & Settled Bill Split: Equal Split at Merchant (3,000 BDT)
        $split1 = $this->billSplitService->createBillSplit(
            initiator: $user1,
            data: [
                'title' => 'Team Lunch at Gloria Jeans',
                'total_amount' => toPaisa(3000),
                'mode' => BillSplitMode::EQUAL,
                'participants' => [
                    ['user_id' => $user1->id],
                    ['user_id' => $user2->id],
                    ['user_id' => $user4->id],
                ],
                'merchant_account_id' => $merchantAccount?->id,
                'merchant_name' => 'Gloria Jeans Coffee',
                'note' => 'Quarterly celebration team lunch',
                'expires_in_days' => 5,
            ]
        );

        // Accept all participant shares to trigger atomic settlement & merchant payout
        foreach ($split1->participants as $participant) {
            if (! $participant->is_initiator) {
                $this->billSplitService->acceptParticipant($participant);
            }
        }

        // B. Pending Bill Split: Percentage Split (6,000 BDT)
        $split2 = $this->billSplitService->createBillSplit(
            initiator: $user2,
            data: [
                'title' => 'Weekend Roadtrip Groceries',
                'total_amount' => toPaisa(6000),
                'mode' => BillSplitMode::PERCENTAGE,
                'participants' => [
                    ['user_id' => $user2->id, 'value' => 40.0],
                    ['user_id' => $user1->id, 'value' => 30.0],
                    ['user_id' => $user3->id, 'value' => 30.0],
                ],
                'note' => 'Groceries and snacks for trip',
                'expires_in_days' => 3,
            ]
        );

        // User 1 accepts their share, placing a hold; User 3 remains pending
        $user1Participant = $split2->participants()->where('user_id', $user1->id)->first();
        if ($user1Participant) {
            $this->billSplitService->acceptParticipant($user1Participant);
        }

        // C. Declined / Failed Bill Split (4,500 BDT)
        $split3 = $this->billSplitService->createBillSplit(
            initiator: $user3,
            data: [
                'title' => 'Concert Tickets',
                'total_amount' => toPaisa(4500),
                'mode' => BillSplitMode::EQUAL,
                'participants' => [
                    ['user_id' => $user3->id],
                    ['user_id' => $user5->id],
                ],
                'note' => 'Rock fest concert entry passes',
                'expires_in_days' => 2,
            ]
        );

        $user5Participant = $split3->participants()->where('user_id', $user5->id)->first();
        if ($user5Participant) {
            $this->billSplitService->rejectParticipant($user5Participant, 'Unable to attend due to prior commitments.');
        }

        // ---------------------------------------------------------------------
        // 7. SYSTEM AUDIT & RECONCILIATION SNAPSHOT
        // ---------------------------------------------------------------------
        // Run full incremental audit to verify ledger invariance and create initial checkpoint
        $this->reconciliationService->auditSystemIntegrity(createCheckpointOnPass: true);
    }
}
