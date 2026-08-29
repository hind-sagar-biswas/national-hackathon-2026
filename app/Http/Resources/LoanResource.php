<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'lender_user_id' => $this->lender_user_id,
            'borrower_user_id' => $this->borrower_user_id,
            'principal_amount' => [
                'raw' => $this->principal_amount,
                'formatted' => number_format(($this->principal_amount ?? 0) / 100, 2),
            ],
            'outstanding_amount' => [
                'raw' => $this->outstanding_amount,
                'formatted' => number_format(($this->outstanding_amount ?? 0) / 100, 2),
            ],
            'status' => $this->status,
            'disbursement_txn_id' => $this->disbursement_txn_id,
            'money_request_id' => $this->money_request_id,
            'note' => $this->note,
            'due_at' => $this->due_at ? [
                'raw' => $this->due_at->toIso8601String(),
                'formatted' => $this->due_at->diffForHumans(),
            ] : null,
            'lender' => UserResource::make($this->whenLoaded('lender')),
            'borrower' => UserResource::make($this->whenLoaded('borrower')),
            'money_request' => MoneyRequestResource::make($this->whenLoaded('moneyRequest')),
            'disbursement_transaction' => TransactionResource::make($this->whenLoaded('disbursementTransaction')),
            'repayments' => LoanRepaymentResource::collection($this->whenLoaded('repayments')),
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
