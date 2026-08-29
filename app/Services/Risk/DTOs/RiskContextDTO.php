<?php

namespace App\Services\Risk\DTOs;

use App\Models\Account;
use App\Models\User;

readonly class RiskContextDTO
{
    public function __construct(
        public ?User $senderUser,
        public Account $senderAccount,
        public Account $receiverAccount,
        public int $amount,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public array $extra = [],
    ) {}

    public static function fromRequest(
        Account $senderAccount,
        Account $receiverAccount,
        int $amount,
        array $extra = [],
    ): self {
        $request = request();

        return new self(
            senderUser: $senderAccount->user,
            senderAccount: $senderAccount,
            receiverAccount: $receiverAccount,
            amount: $amount,
            ipAddress: $request?->ip(),
            userAgent: $request?->userAgent(),
            extra: $extra,
        );
    }
}
