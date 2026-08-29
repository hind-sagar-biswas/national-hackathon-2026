<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoneyRequest extends Model
{
    protected $fillable = [
        'requester_account_id',
        'payer_account_id',
        'amount',
        'status',
        'hold_id',
        'transaction_id',
        'expires_at',
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
