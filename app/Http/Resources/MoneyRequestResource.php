<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoneyRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requester_account_id' => $this->requester_account_id,
            'payer_account_id' => $this->payer_account_id,
            'amount' => [
                'raw' => $this->amount,
                'formatted' => number_format(($this->amount ?? 0) / 100, 2),
            ],
            'type' => $this->type,
            'status' => $this->status,
            'hold_id' => $this->hold_id,
            'transaction_id' => $this->transaction_id,
            'loan_id' => $this->loan_id,
            'note' => $this->note,
            'due_at' => $this->due_at ? [
                'raw' => $this->due_at->toIso8601String(),
                'formatted' => $this->due_at->diffForHumans(),
            ] : null,
            'expires_at' => $this->expires_at ? [
                'raw' => $this->expires_at->toIso8601String(),
                'formatted' => $this->expires_at->diffForHumans(),
            ] : null,
            'requester_account' => AccountResource::make($this->whenLoaded('requesterAccount')),
            'payer_account' => AccountResource::make($this->whenLoaded('payerAccount')),
            'hold' => HoldResource::make($this->whenLoaded('hold')),
            'transaction' => TransactionResource::make($this->whenLoaded('transaction')),
            'loan' => LoanResource::make($this->whenLoaded('loan')),
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
