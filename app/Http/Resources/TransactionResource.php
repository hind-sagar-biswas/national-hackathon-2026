<?php

namespace App\Http\Resources;

use App\Enums\TransactionDirection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amountPaisa = (int) ($this->metadata['amount'] ?? 0);
        if ($amountPaisa === 0 && $this->relationLoaded('ledgerEntries') && $this->ledgerEntries->isNotEmpty()) {
            $debitSum = (int) $this->ledgerEntries->where('direction', TransactionDirection::DEBIT)->sum('amount');
            $amountPaisa = $debitSum > 0 ? $debitSum : (int) $this->ledgerEntries->max('amount');
        }

        $userAccount = $request->user()?->account;
        $userEntry = null;

        if ($this->relationLoaded('ledgerEntries')) {
            if ($userAccount) {
                $userEntry = $this->ledgerEntries->firstWhere('account_id', $userAccount->id);
            }
            if (! $userEntry && $request->user()) {
                $userId = $request->user()->id;
                $userEntry = $this->ledgerEntries->first(fn ($entry) => $entry->account?->user_id === $userId);
            }
        }

        $userDirection = null;
        if ($userEntry) {
            $userDirection = $userEntry->direction instanceof TransactionDirection
                ? $userEntry->direction->value
                : (string) $userEntry->direction;
        }

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'amount' => [
                'raw' => $amountPaisa,
                'formatted' => number_format($amountPaisa / 100, 2),
            ],
            'user_direction' => $userDirection, // 'debit', 'credit', or null
            'idempotency_key' => $this->idempotency_key,
            'initiated_by' => $this->initiated_by,
            'metadata' => $this->metadata,
            'initiator' => UserResource::make($this->whenLoaded('initiator')),
            'ledger_entries' => LedgerEntryResource::collection($this->whenLoaded('ledgerEntries')),
            'loan_repayments' => LoanRepaymentResource::collection($this->whenLoaded('loanRepayments')),
            'created_at' => [
                'raw' => $this->created_at?->toIso8601String(),
                'formatted' => $this->created_at?->diffForHumans(),
            ],
            'updated_at' => [
                'raw' => $this->updated_at?->toIso8601String(),
                'formatted' => $this->updated_at?->diffForHumans(),
            ],
        ];
    }
}
