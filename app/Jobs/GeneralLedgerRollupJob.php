<?php

namespace App\Jobs;

use App\Services\Banking\ReconciliationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneralLedgerRollupJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Execute the job.
     */
    public function handle(ReconciliationService $reconciliationService): void
    {
        try {
            $snapshot = $reconciliationService->rollupGeneralLedger(now());

            Log::info('General ledger rollup snapshot completed successfully', $snapshot);
        } catch (Throwable $e) {
            Log::error('General ledger rollup snapshot failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
