<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountOwner;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\GeneralLedgerSummaryResource;
use App\Models\Account;
use App\Models\GeneralLedgerSummary;
use App\Services\Banking\ReconciliationService;
use Inertia\Inertia;
use Throwable;

class ReconciliationController extends Controller
{
    public function index(ReconciliationService $reconciliationService)
    {
        $systemAccounts = Account::where('is_system', true)->get()->keyBy('slug');

        $platformEquity = $systemAccounts->get('platform_equity');
        $feeIncome = $systemAccounts->get('fee_income');
        $cashReserve = $systemAccounts->get('cash_reserve');

        $totalUserLiabilities = (int) Account::where('owner_type', AccountOwner::USER)->sum('cleared_balance');

        $glSummaries = GeneralLedgerSummary::query()
            ->orderBy('as_of', 'desc')
            ->paginate(config('app.feature.pagination'))
            ->withQueryString();

        $liveAudit = $reconciliationService->auditSystemIntegrity();

        return inertia('Admin/Reconciliation/Index', [
            'systemAccounts' => [
                'platform_equity' => $platformEquity ? AccountResource::make($platformEquity) : null,
                'fee_income' => $feeIncome ? AccountResource::make($feeIncome) : null,
                'cash_reserve' => $cashReserve ? AccountResource::make($cashReserve) : null,
                'total_user_liabilities' => $totalUserLiabilities,
            ],
            'auditReport' => $liveAudit,
            'glSummaries' => Inertia::defer(fn () => GeneralLedgerSummaryResource::collection($glSummaries)),
        ]);
    }

    public function audit(ReconciliationService $reconciliationService)
    {
        try {
            $auditReport = $reconciliationService->auditSystemIntegrity();

            return back()->with([
                'audit_report' => $auditReport,
                'success' => $auditReport['passed']
                    ? 'Mathematical zero-sum ledger audit passed with 0 discrepancies.'
                    : "Audit detected {$auditReport['mismatch_count']} account discrepancies.",
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Audit execution failed: '.$e->getMessage());
        }
    }

    public function rollup(ReconciliationService $reconciliationService)
    {
        try {
            $result = $reconciliationService->rollupGeneralLedger();

            return back()->with('success', 'General ledger category summary snapshot recorded for as of '.$result['as_of']);
        } catch (Throwable $e) {
            return back()->with('error', 'Rollup execution failed: '.$e->getMessage());
        }
    }
}
