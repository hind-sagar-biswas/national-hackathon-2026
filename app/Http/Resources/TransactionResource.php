<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
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
