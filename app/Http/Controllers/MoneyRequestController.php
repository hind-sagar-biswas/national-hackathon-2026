<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\MoneyRequest\IndexRequest;
use App\Http\Requests\MoneyRequest\StoreRequest;
use App\Http\Resources\MoneyRequestResource;
use App\Models\MoneyRequest;
use App\Models\User;
use App\Services\Banking\MoneyRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class MoneyRequestController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $account = $user->account;

        $filters = $request->validated();
        $tab = $filters['tab'] ?? 'incoming';
        $filters['status'] = $filters['status'] ?? null;
        $filters['search'] = $filters['search'] ?? null;

        $query = MoneyRequest::query()
            ->with(['requesterAccount.user', 'payerAccount.user', 'hold', 'transaction'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'outgoing') {
            $query->where('requester_account_id', $account?->id);
        } else {
            $query->where('payer_account_id', $account?->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $requests = $query->paginate(config('app.feature.pagination'))->withQueryString();

        $incomingCount = $account ? MoneyRequest::where('payer_account_id', $account->id)
            ->where('status', RequestStatus::PENDING)
            ->count() : 0;

        $outgoingCount = $account ? MoneyRequest::where('requester_account_id', $account->id)
            ->where('status', RequestStatus::PENDING)
            ->count() : 0;

        return inertia('MoneyRequests/Index', [
            'list' => Inertia::defer(fn () => MoneyRequestResource::collection($requests)),
            'filters' => $filters,
            'tab' => $tab,
            'counts' => [
                'incoming_pending' => $incomingCount,
                'outgoing_pending' => $outgoingCount,
            ],
        ]);
    }

    public function store(StoreRequest $request, MoneyRequestService $moneyRequestService)
    {
        /** @var User $user */
        $user = Auth::user();
        $requesterAccount = $user->account;

        if (! $requesterAccount) {
            throw ValidationException::withMessages([
                'amount' => 'You do not have an active wallet account.',
            ]);
        }

        $payerInput = trim($request->validated('payer'));

        /** @var User|null $payer */
        $payer = User::where('email', $payerInput)
            ->orWhere('phone', $payerInput)
            ->first();

        if (! $payer) {
            throw ValidationException::withMessages([
                'payer' => 'No user found with the provided email address or phone number.',
            ]);
        }

        if ($payer->id === $user->id) {
            throw ValidationException::withMessages([
                'payer' => 'You cannot create a money request to yourself.',
            ]);
        }

        $payerAccount = $payer->account;
        if (! $payerAccount) {
            throw ValidationException::withMessages([
                'payer' => 'The payer wallet is not currently active.',
            ]);
        }

        $amount = toPaisa($request->validated('amount'));
        $expiresInDays = (int) ($request->validated('expires_in_days') ?? 3);
        $expiresAt = now()->addDays($expiresInDays);
        $preHold = (bool) $request->validated('pre_hold', false);

        try {
            $moneyRequestService->createRequest(
                requesterAccount: $requesterAccount,
                payerAccount: $payerAccount,
                amount: $amount,
                expiresAt: $expiresAt,
                preHold: $preHold,
            );

            $formattedAmount = formatPaisa($amount);

            return back()->with('success', "Money request for {$formattedAmount} BDT sent to {$payer->name}.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }
    }

    public function approve(MoneyRequest $moneyRequest, MoneyRequestService $moneyRequestService)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($moneyRequest->payer_account_id !== $user->account?->id) {
            abort(403, 'Unauthorized action on this money request.');
        }

        try {
            $moneyRequestService->approve($moneyRequest);

            return back()->with('success', 'Money request approved and settled successfully.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'request' => $e->getMessage(),
            ]);
        }
    }

    public function reject(MoneyRequest $moneyRequest, MoneyRequestService $moneyRequestService)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($moneyRequest->payer_account_id !== $user->account?->id) {
            abort(403, 'Unauthorized action on this money request.');
        }

        try {
            $moneyRequestService->reject($moneyRequest);

            return back()->with('success', 'Money request declined.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'request' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(MoneyRequest $moneyRequest, MoneyRequestService $moneyRequestService)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($moneyRequest->requester_account_id !== $user->account?->id) {
            abort(403, 'Unauthorized action on this money request.');
        }

        try {
            $moneyRequestService->expire($moneyRequest);

            return back()->with('success', 'Money request cancelled.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'request' => $e->getMessage(),
            ]);
        }
    }
}
