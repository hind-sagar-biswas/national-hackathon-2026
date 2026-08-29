<script setup>
import Button from '@/Components/Buttons/Button.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { audit, index, rollup } from '@/routes/admin/reconciliation';
import { router, usePage } from '@inertiajs/vue3';
import { 
    AlertTriangle, 
    Calculator, 
    CheckCircle2, 
    Database, 
    FileSpreadsheet, 
    RefreshCw, 
    Scale, 
    ShieldCheck, 
    Wallet 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';

const props = defineProps({
    systemAccounts: Object,
    auditReport: Object,
    glSummaries: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Admin - General Ledger & Reconciliation' }],
});

const page = usePage();

const handleRunAudit = () => {
    router.post(audit(), {}, { preserveScroll: true });
};

const handleRollup = () => {
    if (confirm('Record snapshot of General Ledger balances for the current date?')) {
        router.post(rollup(), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Header & Action Controls -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <Scale size="24" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-content">
                            General Ledger & Mathematical Audit
                        </h1>
                        <p class="text-xs text-base-content/60">
                            Zero-sum ledger reconciliation, system accounts oversight, and balance sheet snapshots.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button color="secondary" size="sm" @click="handleRollup">
                        <FileSpreadsheet class="size-4 me-1" /> Record GL Rollup Snapshot
                    </Button>

                    <Button color="primary" size="sm" @click="handleRunAudit">
                        <Calculator class="size-4 me-1" /> Run Zero-Sum Audit
                    </Button>
                </div>
            </div>
        </div>

        <!-- System Accounts & Reserves Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Platform Equity -->
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-4">
                    <span class="text-xs text-base-content/60 font-bold block">Platform Equity</span>
                    <span class="text-xl font-extrabold text-primary">
                        {{ systemAccounts?.platform_equity?.available_balance?.formatted ?? '0.00' }} BDT
                    </span>
                    <span class="text-[10px] text-base-content/50 block font-mono">SYS-EQUITY</span>
                </div>
            </div>

            <!-- Fee Income -->
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-4">
                    <span class="text-xs text-base-content/60 font-bold block">Fee Income</span>
                    <span class="text-xl font-extrabold text-success">
                        {{ systemAccounts?.fee_income?.available_balance?.formatted ?? '0.00' }} BDT
                    </span>
                    <span class="text-[10px] text-base-content/50 block font-mono">SYS-FEE</span>
                </div>
            </div>

            <!-- Cash Reserve -->
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-4">
                    <span class="text-xs text-base-content/60 font-bold block">Cash Reserve</span>
                    <span class="text-xl font-extrabold text-info">
                        {{ systemAccounts?.cash_reserve?.available_balance?.formatted ?? '0.00' }} BDT
                    </span>
                    <span class="text-[10px] text-base-content/50 block font-mono">SYS-RESERVE</span>
                </div>
            </div>

            <!-- Total User Liabilities -->
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-4">
                    <span class="text-xs text-base-content/60 font-bold block">Total User Liabilities</span>
                    <span class="text-xl font-extrabold text-warning">
                        {{ systemAccounts?.total_user_liabilities?.formatted ?? systemAccounts?.total_user_liabilities ?? '0' }} BDT
                    </span>
                    <span class="text-[10px] text-base-content/50 block font-mono">USER-WALLETS</span>
                </div>
            </div>
        </div>

        <!-- Audit Status Widget -->
        <div 
            class="alert shadow-sm border" 
            :class="auditReport?.passed ? 'alert-success border-success/40' : 'alert-error border-error/40'"
        >
            <CheckCircle2 v-if="auditReport?.passed" class="size-6 text-success-content flex-shrink-0" />
            <AlertTriangle v-else class="size-6 text-error-content flex-shrink-0" />

            <div class="flex-1">
                <h3 class="font-bold text-sm">
                    {{ auditReport?.passed ? 'Mathematical Zero-Sum Check Passed' : 'Ledger Discrepancy Detected' }}
                </h3>
                <div class="text-xs opacity-90">
                    {{ auditReport?.passed ? 'All double-entry debits and credits across platform accounts balance to exactly 0.' : `Detected ${auditReport?.mismatch_count || 0} account discrepancy entries.` }}
                </div>
            </div>

            <span class="badge font-mono text-xs font-bold" :class="auditReport?.passed ? 'badge-success' : 'badge-error'">
                As Of: {{ auditReport?.as_of || 'Live' }}
            </span>
        </div>

        <!-- General Ledger Snapshots Table -->
        <div class="card bg-base-100 shadow-md border border-base-300">
            <div class="card-body p-6 space-y-4">
                <h3 class="text-base font-bold text-base-content flex items-center gap-2">
                    <Database class="size-5 text-primary" />
                    <span>General Ledger Rollup History</span>
                </h3>

                <div class="overflow-x-auto">
                    <DataTable :value="glSummaries?.data || []" tableStyle="min-width: 50rem" class="bg-base-100">
                        <Column field="as_of" header="As Of Date">
                            <template #body="slotProps">
                                <span class="font-mono text-xs font-bold text-primary">
                                    {{ slotProps.data.as_of }}
                                </span>
                            </template>
                        </Column>
                        <Column field="total_assets" header="Total Assets">
                            <template #body="slotProps">
                                <span class="font-semibold text-info">
                                    {{ slotProps.data.total_assets?.formatted ?? slotProps.data.total_assets }} BDT
                                </span>
                            </template>
                        </Column>
                        <Column field="total_liabilities" header="Total Liabilities">
                            <template #body="slotProps">
                                <span class="font-semibold text-warning">
                                    {{ slotProps.data.total_liabilities?.formatted ?? slotProps.data.total_liabilities }} BDT
                                </span>
                            </template>
                        </Column>
                        <Column field="total_equity" header="Total Equity">
                            <template #body="slotProps">
                                <span class="font-semibold text-success">
                                    {{ slotProps.data.total_equity?.formatted ?? slotProps.data.total_equity }} BDT
                                </span>
                            </template>
                        </Column>
                        <Column field="is_balanced" header="Ledger Status">
                            <template #body="slotProps">
                                <span class="badge badge-sm font-bold uppercase" :class="slotProps.data.is_balanced ? 'badge-success' : 'badge-error'">
                                    {{ slotProps.data.is_balanced ? 'Balanced' : 'Imbalanced' }}
                                </span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

    </div>
</template>
