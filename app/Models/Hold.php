<?php

namespace App\Models;

use App\Enums\HoldStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Hold extends Model
{
    use Filterable;

    protected $fillable = [
        'account_id',
        'amount',
        'reason',
        'reference_type',
        'reference_id',
        'status',
        'resolved_at',
    ];

    protected array $filterable = [
        'status',
        'account_id',
        'reference_type',
        'account.user_id',
    ];

    protected array $searchable = [
        'reason',
        'account.user.name',
        'account.user.email',
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
