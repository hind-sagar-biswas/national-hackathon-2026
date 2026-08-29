<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\Transaction\IndexRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $account = $user->account;

        $filters = $request->validated();
        $filters['search'] = $filters['search'] ?? null;
        $filters['type'] = $filters['type'] ?? null;

        $query = Transaction::query()
            ->where(function ($q) use ($user, $account) {
                $q->where('initiated_by', $user->id);
                if ($account) {
                    $q->orWhereHas('ledgerEntries', fn ($le) => $le->where('account_id', $account->id));
                }
            })
            ->with(['initiator', 'ledgerEntries.account'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                    ->orWhere('id', 'ilike', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $transactions = $query->paginate(config('app.feature.pagination'))->withQueryString();

        return inertia('Transactions/Index', [
            'list' => Inertia::defer(fn () => TransactionResource::collection($transactions)),
            'filters' => $filters,
            'typeOptions' => EnumUtil::toOptions(TransactionType::class),
        ]);
    }

    public function show(Transaction $transaction)
    {
        /** @var User $user */
        $user = Auth::user();
        $account = $user->account;

        // Ensure user is initiator or participant in this transaction
        $isParticipant = $transaction->initiated_by === $user->id
            || ($account && $transaction->ledgerEntries()->where('account_id', $account->id)->exists());

        if (! $isParticipant && ! $user->hasRole('admin')) {
            abort(403, 'Unauthorized access to transaction receipt.');
        }

        $transaction->load(['initiator', 'ledgerEntries.account.user', 'loanRepayments']);

        return inertia('Transactions/Show', [
            'transaction' => TransactionResource::make($transaction),
        ]);
    }
}
