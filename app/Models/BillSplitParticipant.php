<?php

namespace App\Models;

use App\Enums\BillSplitParticipantStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillSplitParticipant extends Model
{
    use Filterable, HasFactory;

    protected $fillable = [
        'bill_split_id',
        'user_id',
        'account_id',
        'share_amount',
        'share_value',
        'is_initiator',
        'status',
        'hold_id',
        'money_request_id',
        'accepted_at',
    ];

    /**
     * The attributes that can be filtered.
     *
     * @var array<string>
     */
    protected array $filterable = [
        'status',
        'is_initiator',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'share_amount' => 'integer',
            'share_value' => 'float',
            'is_initiator' => 'boolean',
            'status' => BillSplitParticipantStatus::class,
            'accepted_at' => 'datetime',
        ];
    }

    public function billSplit(): BelongsTo
    {
        return $this->belongsTo(BillSplit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }

    public function moneyRequest(): BelongsTo
    {
        return $this->belongsTo(MoneyRequest::class);
    }
}
