<?php

namespace App\Models;

use App\Enums\BillSplitMode;
use App\Enums\BillSplitStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillSplit extends Model
{
    use Filterable, HasFactory;

    protected $fillable = [
        'initiator_user_id',
        'initiator_account_id',
        'title',
        'total_amount',
        'mode',
        'status',
        'merchant_account_id',
        'merchant_name',
        'note',
        'settled_at',
        'expires_at',
        'metadata',
    ];

    /**
     * The attributes that can be filtered.
     *
     * @var array<string>
     */
    protected array $filterable = [
        'status',
        'mode',
    ];

    /**
     * The attributes that can be searched.
     *
     * @var array<string>
     */
    protected array $searchable = [
        'title',
        'merchant_name',
        'note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'mode' => BillSplitMode::class,
            'status' => BillSplitStatus::class,
            'settled_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function initiatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_user_id');
    }

    public function initiatorAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'initiator_account_id');
    }

    public function merchantAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'merchant_account_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BillSplitParticipant::class);
    }
}
