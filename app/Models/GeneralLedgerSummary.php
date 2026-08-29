<?php

namespace App\Models;

use App\Enums\AccountType;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class GeneralLedgerSummary extends Model
{
    use Filterable;

    protected $fillable = [
        'category',
        'total',
        'as_of',
    ];

    protected array $filterable = [
        'category',
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
