<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

class GeneralLedgerSummary extends Model
{
    protected $table = 'general_ledger_summary';

    protected $fillable = [
        'category',
        'total',
        'as_of',
    ];

    protected function casts(): array
    {
        return [
            'category' => AccountType::class,
            'total' => 'integer',
            'as_of' => 'datetime',
        ];
    }
}
