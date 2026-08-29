<?php

namespace App\Models;

use App\Enums\LoanStatus;
use HindBiswas\ModelUtils\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use Filterable;

    protected $fillable = [
        'lender_user_id',
        'borrower_user_id',
        'principal_amount',
        'outstanding_amount',
        'status',
        'disbursement_txn_id',
        'due_at',
        'note',
    ];

    protected array $filterable = [
        'status',
        'lender_user_id',
        'borrower_user_id',
        'lender.email',
        'borrower.email',
    ];

    protected array $searchable = [
        'note',
        'lender.name',
        'lender.email',
        'borrower.name',
        'borrower.email',
        'disbursementTransaction.reference',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'principal_amount' => 'integer',
            'outstanding_amount' => 'integer',
            'due_at' => 'datetime',
        ];
    }

    public function lender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lender_user_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_user_id');
    }

    public function disbursementTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'disbursement_txn_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
