<?php

namespace App\Jobs;

use App\Enums\RequestStatus;
use App\Models\MoneyRequest;
use App\Services\Banking\MoneyRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class HoldExpirySweepJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Execute the job.
     */
    public function handle(MoneyRequestService $moneyRequestService): void
    {
        try {
            $expiredRequests = MoneyRequest::query()
                ->where('status', RequestStatus::PENDING)
                ->where('expires_at', '<=', now())
                ->with(['hold', 'payerAccount', 'requesterAccount'])
                ->get();

            $processedCount = 0;
            $releasedHoldsCount = 0;

            foreach ($expiredRequests as $request) {
                if ($request->hold_id) {
                    $releasedHoldsCount++;
                }

                $moneyRequestService->expire($request);
                $processedCount++;
            }

            if ($processedCount > 0) {
                Log::info('Hold expiry sweep completed', [
                    'expired_requests_processed' => $processedCount,
                    'holds_released' => $releasedHoldsCount,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Hold expiry sweep failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
