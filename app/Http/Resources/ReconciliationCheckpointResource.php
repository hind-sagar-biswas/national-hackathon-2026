<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReconciliationCheckpointResource extends JsonResource
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
            'last_ledger_entry_id' => $this->last_ledger_entry_id,
            'total_debits' => [
                'raw' => $this->total_debits,
                'formatted' => number_format($this->total_debits / 100, 2),
            ],
            'total_credits' => [
                'raw' => $this->total_credits,
                'formatted' => number_format($this->total_credits / 100, 2),
            ],
            'is_balanced' => $this->is_balanced,
            'account_snapshots' => $this->account_snapshots,
            'as_of' => [
                'raw' => $this->as_of?->toIso8601String(),
                'formatted' => $this->as_of?->diffForHumans(),
            ],
            'created_at' => [
                'raw' => $this->created_at?->toIso8601String(),
                'formatted' => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}
