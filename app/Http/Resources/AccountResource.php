<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'owner_type' => $this->owner_type,
            'user_id' => $this->user_id,
            'slug' => $this->slug,
            'category' => $this->category,
            'cleared_balance' => [
                'raw' => $this->cleared_balance,
                'formatted' => number_format(($this->cleared_balance ?? 0) / 100, 2),
            ],
            'available_balance' => [
                'raw' => $this->available_balance,
                'formatted' => number_format(($this->available_balance ?? 0) / 100, 2),
            ],
            'currency' => $this->currency,
            'is_system' => $this->is_system,
            'user' => UserResource::make($this->whenLoaded('user')),
            'ledger_entries' => LedgerEntryResource::collection($this->whenLoaded('ledgerEntries')),
            'holds' => HoldResource::collection($this->whenLoaded('holds')),
            'active_holds' => HoldResource::collection($this->whenLoaded('activeHolds')),
            'outgoing_money_requests' => MoneyRequestResource::collection($this->whenLoaded('outgoingMoneyRequests')),
            'incoming_money_requests' => MoneyRequestResource::collection($this->whenLoaded('incomingMoneyRequests')),
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
