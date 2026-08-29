<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillSplitParticipantResource extends JsonResource
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
            'bill_split_id' => $this->bill_split_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'profile_photo_url' => $this->user->profile_photo_url,
            ]),
            'share_amount' => [
                'raw' => $this->share_amount,
                'formatted' => formatPaisa($this->share_amount),
            ],
            'share_value' => $this->share_value,
            'is_initiator' => $this->is_initiator,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'hold_id' => $this->hold_id,
            'money_request_id' => $this->money_request_id,
            'accepted_at' => [
                'raw' => $this->accepted_at?->toIso8601String(),
                'formatted' => $this->accepted_at?->diffForHumans(),
            ],
            'created_at' => [
                'raw' => $this->created_at?->toIso8601String(),
                'formatted' => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}
