<?php

namespace App\Jobs;

use App\Services\Banking\ReconciliationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class LedgerReconciliationJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Execute the job.
     */
    public function handle(ReconciliationService $reconciliationService): void
    {
        try {
            $audit = $reconciliationService->auditSystemIntegrity();

            if (! $audit['passed']) {
                Log::critical('Automated Ledger Integrity Audit Failed: Mismatches detected', [
                    'mismatch_count' => $audit['mismatch_count'],
                    'mismatches' => $audit['mismatches'],
                    'global_ledger' => $audit['global_ledger'],
                ]);
            } else {
                Log::info('Automated Ledger Integrity Audit Passed: Zero-sum verified', [
                    'accounts_audited' => $audit['accounts_audited'],
                    'total_debits' => $audit['global_ledger']['total_debits'],
                    'total_credits' => $audit['global_ledger']['total_credits'],
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Automated Ledger Integrity Audit execution error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
