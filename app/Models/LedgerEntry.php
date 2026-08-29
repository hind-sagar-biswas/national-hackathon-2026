<?php

namespace App\Models;

use App\Enums\TransactionDirection;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use Filterable;

    protected $fillable = [
        'transaction_id',
        'account_id',
        'direction',
        'amount',
        'balance_after',
    ];

    protected array $filterable = [
        'account_id',
        'transaction_id',
        'direction',
        'account.category',
        'account.owner_type',
        'transaction.type',
    ];

    protected array $searchable = [
        'transaction.reference',
        'account.slug',
        'account.user.name',
        'account.user.email',
    ];

    protected function casts(): array
    {
        return [
            'direction' => TransactionDirection::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
