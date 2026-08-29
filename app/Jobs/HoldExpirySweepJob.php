<?php

namespace App\Jobs;

use App\Enums\BillSplitStatus;
use App\Enums\RequestStatus;
use App\Models\BillSplit;
use App\Models\MoneyRequest;
use App\Services\Banking\BillSplitService;
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
    public function handle(
        MoneyRequestService $moneyRequestService,
        BillSplitService $billSplitService
    ): void {
        try {
            // 1. Sweep expired money requests
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

            // 2. Sweep expired bill splits
            $expiredBillSplits = BillSplit::query()
                ->where('status', BillSplitStatus::PENDING)
                ->where('expires_at', '<=', now())
                ->with(['participants.hold', 'participants.user'])
                ->get();

            $expiredSplitsCount = 0;
            foreach ($expiredBillSplits as $split) {
                $billSplitService->expireBillSplit($split);
                $expiredSplitsCount++;
            }

            if ($processedCount > 0 || $expiredSplitsCount > 0) {
                Log::info('Hold and bill split expiry sweep completed', [
                    'expired_requests_processed' => $processedCount,
                    'holds_released' => $releasedHoldsCount,
                    'expired_bill_splits' => $expiredSplitsCount,
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
