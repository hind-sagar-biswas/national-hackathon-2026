<?php

namespace App\Models;

use App\Enums\HoldStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Hold extends Model
{

    protected $fillable = [
        'account_id',
        'amount',
        'reason',
        'reference_type',
        'reference_id',
        'status',
        'resolved_at',
    ];
    protected function casts(): array
    {
        return [
            'status' => HoldStatus::class,
            'amount' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function moneyRequest(): HasOne
    {
        return $this->hasOne(MoneyRequest::class);
    }
}
