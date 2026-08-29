<?php

namespace App\Models;

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRequest extends Model
{

    protected $fillable = [
        'user_id',
        'provider',
        'provider_ref',
        'amount',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => DepositProvider::class,
            'status' => DepositStatus::class,
            'amount' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
