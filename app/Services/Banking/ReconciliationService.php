<?php

namespace App\Services\Banking;

use App\Enums\AccountOwner;
use App\Enums\AccountType;
use App\Enums\TransactionDirection;
use App\Models\Account;
use App\Models\GeneralLedgerSummary;
use App\Models\LedgerEntry;
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
            $summary = GeneralLedgerSummary::create([
                'category' => $cat->category,
                'total' => (int) $cat->total,
                'as_of' => $asOf,
            ]);

            $results[$cat->category->value] = (int) $cat->total;
        }

        return [
            'as_of' => $asOf->toIso8601String(),
            'totals' => $results,
        ];
    }

    /**
     * Audit mathematical integrity across double-entry ledgers and account balances.
     */
    public function auditSystemIntegrity(): array
    {
        $mismatches = [];

        // 1. Account-by-Account Ledger Consistency Check
        $accounts = Account::all();

        foreach ($accounts as $account) {
            $credits = (int) LedgerEntry::where('account_id', $account->id)
                ->where('direction', TransactionDirection::CREDIT)
                ->sum('amount');

            $debits = (int) LedgerEntry::where('account_id', $account->id)
                ->where('direction', TransactionDirection::DEBIT)
                ->sum('amount');

            // For liability/equity/revenue, credits increase balance, debits decrease
            // For assets/expenses, debits increase balance, credits decrease
            $expectedClearedBalance = match ($account->category) {
                AccountType::ASSET, AccountType::EXPENSE => $debits - $credits,
                default => $credits - $debits,
            };

            // If account was adjusted from neutral 0 baseline
            $difference = $account->cleared_balance - ($credits - $debits);

            if ($difference !== 0) {
                $mismatch = [
                    'account_id' => $account->id,
                    'slug' => $account->slug,
                    'owner_type' => $account->owner_type->value,
                    'category' => $account->category->value,
                    'stored_cleared_balance' => $account->cleared_balance,
                    'ledger_credits' => $credits,
                    'ledger_debits' => $debits,
                    'net_ledger' => $credits - $debits,
                    'discrepancy' => $difference,
                ];

                $mismatches[] = $mismatch;
                Log::critical('Ledger Reconciliation Mismatch Detected', $mismatch);
            }
        }

        // 2. Global Zero-Sum Equation Check (Total Debits == Total Credits)
        $totalLedgerDebits = (int) LedgerEntry::where('direction', TransactionDirection::DEBIT)->sum('amount');
        $totalLedgerCredits = (int) LedgerEntry::where('direction', TransactionDirection::CREDIT)->sum('amount');
        $globalLedgerBalanced = ($totalLedgerDebits === $totalLedgerCredits);

        if (! $globalLedgerBalanced) {
            Log::critical('Global Ledger Zero-Sum Failure', [
                'total_debits' => $totalLedgerDebits,
                'total_credits' => $totalLedgerCredits,
                'delta' => $totalLedgerDebits - $totalLedgerCredits,
            ]);
        }

        // 3. User Wallets vs Platform Equity Check
        $userWalletsTotal = (int) Account::where('owner_type', AccountOwner::USER)->sum('cleared_balance');
        $platformEquity = (int) (Account::where('slug', 'platform_equity')->value('cleared_balance') ?? 0);

        return [
            'passed' => empty($mismatches) && $globalLedgerBalanced,
            'accounts_audited' => $accounts->count(),
            'mismatch_count' => count($mismatches),
            'mismatches' => $mismatches,
            'global_ledger' => [
                'total_debits' => $totalLedgerDebits,
                'total_credits' => $totalLedgerCredits,
                'balanced' => $globalLedgerBalanced,
            ],
            'user_wallets_total' => $userWalletsTotal,
            'platform_equity_cleared' => $platformEquity,
        ];
    }
}
