<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Transaction\IndexRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(IndexRequest $request)
    {
        $filters = $request->validated();
        $filters['type'] = $filters['type'] ?? null;
        $filters['search'] = $filters['search'] ?? null;
        $filters['date_from'] = $filters['date_from'] ?? null;
        $filters['date_to'] = $filters['date_to'] ?? null;

        $query = Transaction::query()
            ->with(['initiator', 'ledgerEntries.account.user'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                    ->orWhere('id', 'ilike', "%{$search}%")
                    ->orWhereHas('initiator', function ($iq) use ($search) {
                        $iq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $transactions = $query->paginate(config('app.feature.pagination'))->withQueryString();

        return inertia('Admin/Transactions/Index', [
            'list' => Inertia::defer(fn () => TransactionResource::collection($transactions)),
            'filters' => $filters,
            'typeOptions' => EnumUtil::toOptions(TransactionType::class),
        ]);
    }
}
