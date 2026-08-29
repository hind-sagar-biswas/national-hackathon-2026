<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationEventResource extends JsonResource
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
            'operation_key' => $this->operation_key,
            'status' => $this->status,
            'from_account_id' => $this->from_account_id,
            'to_account_id' => $this->to_account_id,
            'amount' => [
                'raw' => $this->amount,
                'formatted' => number_format(($this->amount ?? 0) / 100, 2),
            ],
            'metadata' => $this->metadata,
            'from_account' => AccountResource::make($this->whenLoaded('fromAccount')),
            'to_account' => AccountResource::make($this->whenLoaded('toAccount')),
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
