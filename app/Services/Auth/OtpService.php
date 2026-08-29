<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\Banking\TransferOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

class OtpService
{
    protected const DEFAULT_TTL_SECONDS = 300; // 5 minutes

    protected const COOLDOWN_SECONDS = 60; // 1 minute resend throttle

    protected const MAX_ATTEMPTS = 3;

    /**
     * Generate a 6-digit OTP, store in Redis, and dispatch notification to user.
     */
    public function generateAndSend(
        User $user,
        string $action = 'transfer',
        int $ttlSeconds = self::DEFAULT_TTL_SECONDS
    ): string {
        $cooldownKey = "otp_cooldown:{$user->id}:{$action}";

        // Enforce 60-second resend cooldown
        if (Redis::get($cooldownKey)) {
            $ttlRemaining = Redis::ttl($cooldownKey);

            throw new RuntimeException("Please wait {$ttlRemaining} seconds before requesting a new OTP.");
        }

        // Generate 6-digit cryptographic code
        $code = sprintf('%06d', random_int(0, 999999));

        $otpKey = "otp:{$user->id}:{$action}";
        $data = [
            'hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($ttlSeconds)->timestamp,
        ];

        Redis::setex($otpKey, $ttlSeconds, json_encode($data));
        Redis::setex($cooldownKey, self::COOLDOWN_SECONDS, '1');

        // Dispatch notification (email, database, real-time broadcast)
        $user->notify(new TransferOtpNotification($code, (int) ceil($ttlSeconds / 60)));

        return $code;
    }

    /**
     * Verify an OTP code provided by the user for a specific action.
     */
    public function verify(User $user, string $code, string $action = 'transfer'): bool
    {
        $otpKey = "otp:{$user->id}:{$action}";
        $cached = Redis::get($otpKey);

        if (! $cached) {
            return false;
        }

        $data = json_decode($cached, true);
        if (! is_array($data) || ! isset($data['hash'])) {
            return false;
        }

        // Check if max attempts reached
        if (($data['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            Redis::del($otpKey);

            return false;
        }

        // Verify hash
        if (Hash::check($code, $data['hash'])) {
            // Validated successfully: clear OTP key immediately
            Redis::del($otpKey);

            return true;
        }

        // Record failed attempt
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $remainingTtl = max(1, $data['expires_at'] - now()->timestamp);

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            Redis::del($otpKey);
        } else {
            Redis::setex($otpKey, $remainingTtl, json_encode($data));
        }

        return false;
    }

    /**
     * Invalidate any active OTP for a user and action.
     */
    public function clear(User $user, string $action = 'transfer'): void
    {
        Redis::del("otp:{$user->id}:{$action}");
        Redis::del("otp_cooldown:{$user->id}:{$action}");
    }
}
