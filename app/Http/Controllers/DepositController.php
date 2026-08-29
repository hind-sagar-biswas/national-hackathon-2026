<?php

namespace App\Http\Controllers;

use App\Enums\DepositProvider;
use App\Http\Requests\Deposit\IndexRequest;
use App\Http\Requests\Deposit\StoreRequest;
use App\Http\Resources\DepositRequestResource;
use App\Models\DepositRequest;
use App\Models\User;
use App\Services\Banking\DepositService;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class DepositController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $filters = $request->validated();
        $filters['status'] = $filters['status'] ?? null;
        $filters['provider'] = $filters['provider'] ?? null;
        $filters['search'] = $filters['search'] ?? null;

        $query = DepositRequest::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('provider_ref', 'ilike', "%{$search}%");
        }

        $deposits = $query->paginate(config('app.feature.pagination'))->withQueryString();

        return inertia('Deposits/Index', [
            'list' => Inertia::defer(fn () => DepositRequestResource::collection($deposits)),
            'filters' => $filters,
            'providerOptions' => EnumUtil::toOptions(DepositProvider::class),
        ]);
    }

    public function store(StoreRequest $request, DepositService $depositService)
    {
        /** @var User $user */
        $user = Auth::user();

        $provider = DepositProvider::from($request->validated('provider'));
        $providerRef = trim($request->validated('provider_ref'));
        $amount = (int) $request->validated('amount');

        try {
            $depositService->initiate(
                user: $user,
                provider: $provider,
                providerRef: $providerRef,
                amount: $amount,
            );

            return back()->with('success', 'Your deposit request has been submitted and is pending verification.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }
    }
}
