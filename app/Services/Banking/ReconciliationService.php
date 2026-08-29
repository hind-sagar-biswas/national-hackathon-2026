<?php

namespace App\Services\Banking;

use App\Enums\AccountOwner;
use App\Enums\TransactionDirection;
use App\Models\Account;
use App\Models\GeneralLedgerSummary;
use App\Models\LedgerEntry;
use App\Models\ReconciliationCheckpoint;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    /**
     * Snapshot account category balance totals into general_ledger_summaries.
     */
    public function rollupGeneralLedger(?CarbonInterface $asOf = null): array
    {
        $asOf = $asOf ?: now();

        $categoryTotals = Account::query()
            ->select('category', DB::raw('SUM(cleared_balance) as total'))
            ->groupBy('category')
            ->get();

        $results = [];

        foreach ($categoryTotals as $cat) {
            GeneralLedgerSummary::create([
                'category' => $cat->category,
                'total' => (int) $cat->total,
                'as_of' => $asOf,
            ]);

            $results[$cat->category->value] = (int) $cat->total;
        }

        // Also trigger incremental checkpoint snapshot to keep watermarks up to date
        $this->auditSystemIntegrity(createCheckpointOnPass: true);

        return [
            'as_of' => $asOf->toIso8601String(),
            'totals' => $results,
        ];
    }

    /**
     * High-performance incremental audit using watermarked checkpoints and grouped SQL.
     */
    public function auditSystemIntegrity(bool $createCheckpointOnPass = true): array
    {
        // 1. Fetch latest baseline checkpoint
        /** @var ReconciliationCheckpoint|null $latestCheckpoint */
        $latestCheckpoint = ReconciliationCheckpoint::orderBy('id', 'desc')->first();

        $watermarkId = $latestCheckpoint ? (int) $latestCheckpoint->last_ledger_entry_id : 0;
        $baselineDebits = $latestCheckpoint ? (int) $latestCheckpoint->total_debits : 0;
        $baselineCredits = $latestCheckpoint ? (int) $latestCheckpoint->total_credits : 0;
        $accountState = $latestCheckpoint && is_array($latestCheckpoint->account_snapshots)
            ? $latestCheckpoint->account_snapshots
            : [];

        // 2. Single Grouped SQL Query for new delta entries created after watermark
        $deltaGrouped = LedgerEntry::query()
            ->select(
                'account_id',
                'direction',
                DB::raw('SUM(amount) as delta_sum'),
                DB::raw('MAX(id) as max_id')
            )
            ->where('id', '>', $watermarkId)
            ->groupBy('account_id', 'direction')
            ->get();

        $newMaxId = $watermarkId;
        $deltaDebits = 0;
        $deltaCredits = 0;

        // 3. Incrementally merge delta onto baseline
        foreach ($deltaGrouped as $row) {
            $accountId = (int) $row->account_id;
            $direction = $row->direction instanceof TransactionDirection
                ? $row->direction
                : TransactionDirection::from($row->direction);
            $amount = (int) $row->delta_sum;
            $maxEntryId = (int) $row->max_id;

            $newMaxId = max($newMaxId, $maxEntryId);

            if (! isset($accountState[$accountId])) {
                $accountState[$accountId] = [
                    'credits' => 0,
                    'debits' => 0,
                    'cleared_balance' => 0,
                ];
            }

            if ($direction === TransactionDirection::CREDIT) {
                $accountState[$accountId]['credits'] = ($accountState[$accountId]['credits'] ?? 0) + $amount;
                $deltaCredits += $amount;
            } else {
                $accountState[$accountId]['debits'] = ($accountState[$accountId]['debits'] ?? 0) + $amount;
                $deltaDebits += $amount;
            }

            $accountState[$accountId]['cleared_balance'] =
                $accountState[$accountId]['credits'] - $accountState[$accountId]['debits'];
        }

        $cumulativeDebits = $baselineDebits + $deltaDebits;
        $cumulativeCredits = $baselineCredits + $deltaCredits;
        $globalBalanced = ($cumulativeDebits === $cumulativeCredits);

        if (! $globalBalanced) {
            Log::critical('Global Ledger Invariant Failure in Incremental Audit', [
                'total_debits' => $cumulativeDebits,
                'total_credits' => $cumulativeCredits,
                'delta' => $cumulativeDebits - $cumulativeCredits,
            ]);
        }

        // 4. Batch verification of accounts in a single query
        $accounts = Account::all();
        $mismatches = [];

        foreach ($accounts as $account) {
            $storedBalance = (int) $account->cleared_balance;
            $computedBalance = isset($accountState[$account->id])
                ? (int) ($accountState[$account->id]['cleared_balance'] ?? 0)
                : 0;

            $discrepancy = $storedBalance - $computedBalance;

            if ($discrepancy !== 0) {
                $mismatch = [
                    'account_id' => $account->id,
                    'slug' => $account->slug,
                    'owner_type' => $account->owner_type->value,
                    'category' => $account->category->value,
                    'stored_cleared_balance' => $storedBalance,
                    'computed_cleared_balance' => $computedBalance,
                    'discrepancy' => $discrepancy,
                ];

                $mismatches[] = $mismatch;
                Log::critical('Account Ledger Discrepancy Detected', $mismatch);
            }
        }

        $auditPassed = empty($mismatches) && $globalBalanced;

        // 5. Persist new checkpoint snapshot if audit passed and there is new data or no previous checkpoint
        if ($auditPassed && $createCheckpointOnPass && ($newMaxId > $watermarkId || ! $latestCheckpoint)) {
            ReconciliationCheckpoint::create([
                'last_ledger_entry_id' => $newMaxId,
                'total_debits' => $cumulativeDebits,
                'total_credits' => $cumulativeCredits,
                'is_balanced' => true,
                'account_snapshots' => $accountState,
                'as_of' => now(),
            ]);
        }

        $userWalletsTotal = (int) Account::where('owner_type', AccountOwner::USER)->sum('cleared_balance');
        $platformEquity = (int) (Account::where('slug', 'platform_equity')->value('cleared_balance') ?? 0);

        return [
            'passed' => $auditPassed,
            'watermark_id' => $watermarkId,
            'new_watermark_id' => $newMaxId,
            'delta_entries_processed' => $deltaGrouped->count(),
            'accounts_audited' => $accounts->count(),
            'mismatch_count' => count($mismatches),
            'mismatches' => $mismatches,
            'global_ledger' => [
                'total_debits' => $cumulativeDebits,
                'total_credits' => $cumulativeCredits,
                'balanced' => $globalBalanced,
            ],
            'user_wallets_total' => $userWalletsTotal,
            'platform_equity_cleared' => $platformEquity,
        ];
    }
}
