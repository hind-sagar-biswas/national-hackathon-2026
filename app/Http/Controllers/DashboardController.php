<?php

namespace App\Http\Controllers;

use App\Enums\AccountOwner;
use App\Enums\DepositStatus;
use App\Enums\HoldStatus;
use App\Enums\LoanStatus;
use App\Enums\Permission;
use App\Enums\RequestStatus;
use App\Http\Resources\AccountResource;
use App\Http\Resources\DepositRequestResource;
use App\Http\Resources\OperationEventResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\DepositRequest;
use App\Models\Hold;
use App\Models\Loan;
use App\Models\MoneyRequest;
use App\Models\OperationEvent;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Admin Dashboard
        if ($user->hasPermissionTo(Permission::VIEW_ADMIN_DASHBOARD->value) || $user->hasRole('admin')) {
            $systemAccounts = Account::where('is_system', true)->get()->keyBy('slug');

            $platformEquity = $systemAccounts->get('platform_equity');
            $feeIncome = $systemAccounts->get('fee_income');
            $cashReserve = $systemAccounts->get('cash_reserve');

            $totalUserLiabilities = (int) Account::where('owner_type', AccountOwner::USER)->sum('cleared_balance');
            $totalHeldAmount = (int) Hold::where('status', HoldStatus::ACTIVE)->sum('amount');
            $totalActiveLoans = (int) Loan::whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PARTIAL])->sum('outstanding_amount');
            $pendingDepositsCount = DepositRequest::where('status', DepositStatus::PENDING)->count();
            $activeHoldsCount = Hold::where('status', HoldStatus::ACTIVE)->count();
            $totalUsersCount = User::count();

            $recentOperations = OperationEvent::query()
                ->with(['fromAccount.user', 'toAccount.user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $pendingDeposits = DepositRequest::query()
                ->where('status', DepositStatus::PENDING)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return inertia('Admin/Dashboard', [
                'systemAccounts' => [
                    'platform_equity' => $platformEquity ? AccountResource::make($platformEquity) : null,
                    'fee_income' => $feeIncome ? AccountResource::make($feeIncome) : null,
                    'cash_reserve' => $cashReserve ? AccountResource::make($cashReserve) : null,
                    'total_user_liabilities' => $totalUserLiabilities,
                ],
                'metrics' => [
                    'total_users_count' => $totalUsersCount,
                    'pending_deposits_count' => $pendingDepositsCount,
                    'active_holds_count' => $activeHoldsCount,
                    'total_held_amount' => $totalHeldAmount,
                    'total_active_loans' => $totalActiveLoans,
                ],
                'recentOperations' => Inertia::defer(fn () => OperationEventResource::collection($recentOperations)),
                'pendingDeposits' => Inertia::defer(fn () => DepositRequestResource::collection($pendingDeposits)),
            ]);
        }

        // 2. User Dashboard
        $account = $user->account;
        if ($account) {
            $account->load('activeHolds');
        }

        // Recent transactions involving the user's account
        $recentTransactions = $account ? Transaction::query()
            ->whereHas('ledgerEntries', fn ($q) => $q->where('account_id', $account->id))
            ->with(['initiator', 'ledgerEntries.account'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        // Pending incoming money requests
        $pendingRequestsCount = $account ? MoneyRequest::where('payer_account_id', $account->id)
            ->where('status', RequestStatus::PENDING)
            ->count() : 0;

        // Loans summary
        $activeLoansBorrowed = Loan::where('borrower_user_id', $user->id)
            ->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PARTIAL])
            ->sum('outstanding_amount');

        $activeLoansLent = Loan::where('lender_user_id', $user->id)
            ->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PARTIAL])
            ->sum('outstanding_amount');

        $totalHeldAmount = $account ? $account->holds()
            ->where('status', HoldStatus::ACTIVE)
            ->sum('amount') : 0;

        return inertia('Dashboard', [
            'account' => $account ? AccountResource::make($account) : null,
            'recentTransactions' => Inertia::defer(fn () => TransactionResource::collection($recentTransactions)),
            'metrics' => [
                'pending_requests_count' => $pendingRequestsCount,
                'loans_borrowed_total' => (int) $activeLoansBorrowed,
                'loans_lent_total' => (int) $activeLoansLent,
                'total_held_amount' => (int) $totalHeldAmount,
            ],
        ]);
    }
}
