<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Deposit\IndexRequest;
use App\Http\Requests\Admin\Deposit\RejectRequest;
use App\Http\Resources\DepositRequestResource;
use App\Models\DepositRequest;
use App\Services\Banking\DepositService;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class DepositController extends Controller
{
    public function index(IndexRequest $request)
    {
        $filters = $request->validated();
        $filters['status'] = $filters['status'] ?? null;
        $filters['provider'] = $filters['provider'] ?? null;
        $filters['search'] = $filters['search'] ?? null;
        $filters['date_from'] = $filters['date_from'] ?? null;
        $filters['date_to'] = $filters['date_to'] ?? null;

        $query = DepositRequest::query()
            ->with(['user.account'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('provider_ref', 'ilike', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%")
                            ->orWhere('phone', 'ilike', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $deposits = $query->paginate(config('app.feature.pagination'))->withQueryString();

        $pendingCount = DepositRequest::where('status', DepositStatus::PENDING)->count();
        $confirmedCount = DepositRequest::where('status', DepositStatus::CONFIRMED)->count();
        $failedCount = DepositRequest::where('status', DepositStatus::FAILED)->count();

        return inertia('Admin/Deposits/Index', [
            'list' => Inertia::defer(fn () => DepositRequestResource::collection($deposits)),
            'filters' => $filters,
            'providerOptions' => EnumUtil::toOptions(DepositProvider::class),
            'statusOptions' => EnumUtil::toOptions(DepositStatus::class),
            'counts' => [
                'pending' => $pendingCount,
                'confirmed' => $confirmedCount,
                'failed' => $failedCount,
            ],
        ]);
    }

    public function confirm(DepositRequest $depositRequest, DepositService $depositService)
    {
        try {
            $depositService->confirm($depositRequest);

            $formattedAmount = formatPaisa($depositRequest->amount);

            return back()->with('success', "Deposit #{$depositRequest->id} for {$formattedAmount} BDT confirmed and credited to user wallet.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'deposit' => $e->getMessage(),
            ]);
        }
    }

    public function reject(DepositRequest $depositRequest, RejectRequest $request, DepositService $depositService)
    {
        $reason = $request->validated('reason');

        try {
            $depositService->reject($depositRequest, $reason);

            return back()->with('success', "Deposit #{$depositRequest->id} has been marked as rejected.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'deposit' => $e->getMessage(),
            ]);
        }
    }
}
