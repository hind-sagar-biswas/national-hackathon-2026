<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoldResource extends JsonResource
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
            'account_id' => $this->account_id,
            'amount' => [
                'raw' => $this->amount,
                'formatted' => number_format(($this->amount ?? 0) / 100, 2),
            ],
            'reason' => $this->reason,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at ? [
                'raw' => $this->resolved_at->toIso8601String(),
                'formatted' => $this->resolved_at->diffForHumans(),
            ] : null,
            'account' => AccountResource::make($this->whenLoaded('account')),
            'money_request' => MoneyRequestResource::make($this->whenLoaded('moneyRequest')),
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
