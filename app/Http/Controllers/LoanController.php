<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Http\Requests\Loan\IndexRequest;
use App\Http\Requests\Loan\RepayRequest;
use App\Http\Requests\Loan\StoreRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Models\User;
use App\Services\Banking\LoanService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class LoanController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $filters = $request->validated();
        $tab = $filters['tab'] ?? 'given';
        $filters['status'] = $filters['status'] ?? null;
        $filters['search'] = $filters['search'] ?? null;

        $query = Loan::query()
            ->with(['lender', 'borrower', 'repayments'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'taken') {
            $query->where('borrower_user_id', $user->id);
        } else {
            $query->where('lender_user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $loans = $query->paginate(config('app.feature.pagination'))->withQueryString();

        $totalLentActive = Loan::where('lender_user_id', $user->id)
            ->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PARTIAL])
            ->sum('outstanding_amount');

        $totalBorrowedActive = Loan::where('borrower_user_id', $user->id)
            ->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PARTIAL])
            ->sum('outstanding_amount');

        return inertia('Loans/Index', [
            'list' => Inertia::defer(fn () => LoanResource::collection($loans)),
            'filters' => $filters,
            'tab' => $tab,
            'stats' => [
                'total_lent_active' => (int) $totalLentActive,
                'total_borrowed_active' => (int) $totalBorrowedActive,
            ],
        ]);
    }

    public function show(Loan $loan)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($loan->lender_user_id !== $user->id && $loan->borrower_user_id !== $user->id) {
            abort(403, 'Unauthorized access to loan details.');
        }

        $loan->load(['lender', 'borrower', 'disbursementTransaction', 'repayments.transaction']);

        return inertia('Loans/Show', [
            'loan' => LoanResource::make($loan),
            'is_lender' => $loan->lender_user_id === $user->id,
        ]);
    }

    public function store(StoreRequest $request, LoanService $loanService)
    {
        /** @var User $lender */
        $lender = Auth::user();

        $borrowerInput = trim($request->validated('borrower'));

        /** @var User|null $borrower */
        $borrower = User::where('email', $borrowerInput)
            ->orWhere('phone', $borrowerInput)
            ->first();

        if (! $borrower) {
            throw ValidationException::withMessages([
                'borrower' => 'No user found with the provided email address or phone number.',
            ]);
        }

        if ($borrower->id === $lender->id) {
            throw ValidationException::withMessages([
                'borrower' => 'You cannot disburse a loan to yourself.',
            ]);
        }

        $principalAmount = (int) $request->validated('principal_amount');
        $dueAt = $request->validated('due_at') ? Carbon::parse($request->validated('due_at')) : null;
        $note = $request->validated('note');
        $idempotencyKey = $request->validated('idempotency_key');

        try {
            $loan = $loanService->disburse(
                lender: $lender,
                borrower: $borrower,
                principalAmount: $principalAmount,
                dueAt: $dueAt,
                note: $note,
                idempotencyKey: $idempotencyKey,
            );

            return back()->with('success', "Loan #{$loan->id} of {$principalAmount} BDT disbursed successfully to {$borrower->name}.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'principal_amount' => $e->getMessage(),
            ]);
        }
    }

    public function repay(Loan $loan, RepayRequest $request, LoanService $loanService)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($loan->borrower_user_id !== $user->id) {
            abort(403, 'Only the borrower can repay this loan.');
        }

        $amount = (int) $request->validated('amount');
        $idempotencyKey = $request->validated('idempotency_key');

        try {
            $loanService->repay(
                loan: $loan,
                amount: $amount,
                idempotencyKey: $idempotencyKey,
            );

            return back()->with('success', "Repayment of {$amount} BDT processed successfully.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }
    }

    public function waive(Loan $loan, LoanService $loanService)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($loan->lender_user_id !== $user->id) {
            abort(403, 'Only the lender can waive this loan.');
        }

        try {
            $loanService->waive($loan, $user);

            return back()->with('success', "Remaining debt on Loan #{$loan->id} has been waived.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'loan' => $e->getMessage(),
            ]);
        }
    }
}
