<?php

namespace App\Services\Risk\DTOs;

use App\Models\Account;
use App\Models\User;

/**
 * Data Transfer Object representing the contextual environment of a transaction evaluated for risk.
 */
readonly class RiskContextDTO
{
    /**
     * Create a new RiskContextDTO instance.
     *
     * @param  User|null  $senderUser  The user initiating the transfer
     * @param  Account  $senderAccount  The source account being debited
     * @param  Account  $receiverAccount  The destination account being credited
     * @param  int  $amount  The transfer amount in smallest currency units (paisa/cents)
     * @param  string|null  $ipAddress  Client IP address from incoming HTTP request
     * @param  string|null  $userAgent  Client User-Agent header from incoming HTTP request
     * @param  array<string, mixed>  $extra  Additional contextual attributes
     */
    public function __construct(
        public ?User $senderUser,
        public Account $senderAccount,
        public Account $receiverAccount,
        public int $amount,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public array $extra = [],
    ) {}

    /**
     * Create a RiskContextDTO instance automatically extracting request headers and user data.
     *
     * @param  Account  $senderAccount  The source debit account
     * @param  Account  $receiverAccount  The destination credit account
     * @param  int  $amount  The transaction amount in smallest currency units (paisa/cents)
     * @param  array<string, mixed>  $extra  Optional contextual metadata
     */
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
