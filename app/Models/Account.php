<?php

namespace App\Models;

use App\Enums\AccountOwner;
use App\Enums\AccountType;
use App\Enums\HoldStatus;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'user_id',
        'slug',
        'category',
        'cleared_balance',
        'available_balance',
        'currency',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'owner_type' => AccountOwner::class,
            'category' => AccountType::class,
            'cleared_balance' => 'integer',
            'available_balance' => 'integer',
            'is_system' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    public function activeHolds(): HasMany
    {
        return $this->hasMany(Hold::class)->where('status', HoldStatus::ACTIVE);
    }

    public function outgoingMoneyRequests(): HasMany
    {
        return $this->hasMany(MoneyRequest::class, 'payer_account_id');
    }

    public function incomingMoneyRequests(): HasMany
    {
        return $this->hasMany(MoneyRequest::class, 'requester_account_id');
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
