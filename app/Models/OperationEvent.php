<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationEvent extends Model
{
    use Filterable;

    protected $fillable = [
        'operation_key',
        'status',
        'from_account_id',
        'to_account_id',
        'amount',
        'metadata',
    ];

    protected array $filterable = [
        'status',
        'from_account_id',
        'to_account_id',
    ];

    protected array $searchable = [
        'operation_key',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'amount' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
