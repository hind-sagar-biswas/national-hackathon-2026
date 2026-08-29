<?php

namespace App\Models;

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRequest extends Model
{
    use Filterable;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_ref',
        'amount',
        'status',
        'confirmed_at',
    ];

    protected array $filterable = [
        'status',
        'provider',
        'user_id',
        'user.email',
    ];

    protected array $searchable = [
        'provider_ref',
        'user.name',
        'user.email',
        'user.phone',
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
