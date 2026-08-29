<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillSplitResource extends JsonResource
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
            'title' => $this->title,
            'total_amount' => [
                'raw' => $this->total_amount,
                'formatted' => formatPaisa($this->total_amount),
            ],
            'mode' => $this->mode->value,
            'mode_label' => $this->mode->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'initiator' => $this->whenLoaded('initiatorUser', fn () => [
                'id' => $this->initiatorUser->id,
                'name' => $this->initiatorUser->name,
                'email' => $this->initiatorUser->email,
                'profile_photo_url' => $this->initiatorUser->profile_photo_url,
            ]),
            'merchant_name' => $this->merchant_name,
            'note' => $this->note,
            'participants' => BillSplitParticipantResource::collection($this->whenLoaded('participants')),
            'participants_count' => $this->participants_count ?? $this->participants()->count(),
            'settled_at' => [
                'raw' => $this->settled_at?->toIso8601String(),
                'formatted' => $this->settled_at?->diffForHumans(),
            ],
            'expires_at' => [
                'raw' => $this->expires_at?->toIso8601String(),
                'formatted' => $this->expires_at?->diffForHumans(),
            ],
            'created_at' => [
                'raw' => $this->created_at?->toIso8601String(),
                'formatted' => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}
