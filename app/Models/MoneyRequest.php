<?php

namespace App\Models;

use App\Enums\RequestStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyRequest extends Model
{
    use Filterable;

    protected $fillable = [
        'requester_account_id',
        'payer_account_id',
        'amount',
        'status',
        'hold_id',
        'transaction_id',
        'expires_at',
    ];

    protected array $filterable = [
        'status',
        'requester_account_id',
        'payer_account_id',
        'requesterAccount.user_id',
        'payerAccount.user_id',
    ];

    protected array $searchable = [
        'requesterAccount.user.name',
        'requesterAccount.user.email',
        'payerAccount.user.name',
        'payerAccount.user.email',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'amount' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function requesterAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'requester_account_id');
    }

    public function payerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payer_account_id');
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
