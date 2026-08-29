<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanRepaymentResource extends JsonResource
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
            'loan_id' => $this->loan_id,
            'transaction_id' => $this->transaction_id,
            'amount' => [
                'raw' => $this->amount,
                'formatted' => number_format(($this->amount ?? 0) / 100, 2),
            ],
            'loan' => LoanResource::make($this->whenLoaded('loan')),
            'transaction' => TransactionResource::make($this->whenLoaded('transaction')),
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
