<?php

namespace App\Models;

use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconciliationCheckpoint extends Model
{
    use Filterable, HasFactory;

    protected $fillable = [
        'last_ledger_entry_id',
        'total_debits',
        'total_credits',
        'is_balanced',
        'account_snapshots',
        'as_of',
    ];

    /**
     * The attributes that can be filtered.
     *
     * @var array<string>
     */
    protected array $filterable = [
        'is_balanced',
    ];

    /**
     * The attributes that can be searched.
     *
     * @var array<string>
     */
    protected array $searchable = [
        'id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_ledger_entry_id' => 'integer',
            'total_debits' => 'integer',
            'total_credits' => 'integer',
            'is_balanced' => 'boolean',
            'account_snapshots' => 'array',
            'as_of' => 'datetime',
        ];
    }
}
