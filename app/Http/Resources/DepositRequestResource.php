<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositRequestResource extends JsonResource
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
            'user_id' => $this->user_id,
            'provider' => $this->provider,
            'provider_ref' => $this->provider_ref,
            'amount' => [
                'raw' => $this->amount,
                'formatted' => number_format(($this->amount ?? 0) / 100, 2),
            ],
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at ? [
                'raw' => $this->confirmed_at->toIso8601String(),
                'formatted' => $this->confirmed_at->diffForHumans(),
            ] : null,
            'user' => UserResource::make($this->whenLoaded('user')),
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
