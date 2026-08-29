<?php

namespace App\Models;

use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use Filterable;

    protected $fillable = [
        'loan_id',
        'transaction_id',
        'amount',
    ];

    protected array $filterable = [
        'loan_id',
        'transaction_id',
        'loan.lender_user_id',
        'loan.borrower_user_id',
        'loan.status',
    ];

    protected array $searchable = [
        'transaction.reference',
        'loan.lender.name',
        'loan.borrower.name',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
