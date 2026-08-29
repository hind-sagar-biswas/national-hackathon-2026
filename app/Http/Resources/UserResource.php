<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'nid' => $this->nid,
            'is_active' => $this->is_active,
            'role' => $this->getRoleNames(),
            'profile_photo_url' => $this->profile_photo_url,
            'email_verified_at' => $this->email_verified_at ? [
                'raw' => $this->email_verified_at->toIso8601String(),
                'formatted' => $this->email_verified_at->diffForHumans(),
            ] : null,
            'admin' => AdminResource::make($this->whenLoaded('admin')),
            'account' => AccountResource::make($this->whenLoaded('account')),
            'loans_given' => LoanResource::collection($this->whenLoaded('loansGiven')),
            'loans_received' => LoanResource::collection($this->whenLoaded('loansReceived')),
            'deposit_requests' => DepositRequestResource::collection($this->whenLoaded('depositRequests')),
            'transactions_initiated' => TransactionResource::collection($this->whenLoaded('transactionsInitiated')),
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
