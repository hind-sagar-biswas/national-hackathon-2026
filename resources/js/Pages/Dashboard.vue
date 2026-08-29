<script setup>
import Button from '@/Components/Buttons/Button.vue';
import { useAuth } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { index as billSplitsIndex } from '@/routes/bill-splits';
import { index as depositsIndex } from '@/routes/deposits';
import { index as loansIndex } from '@/routes/loans';
import { index as moneyRequestsIndex } from '@/routes/money-requests';
import { index as transactionsIndex, show as transactionsShow } from '@/routes/transactions';
import { index as transfersIndex } from '@/routes/transfers';
import { Deferred } from '@inertiajs/vue3';
import { 
    ArrowDownLeft, 
    ArrowUpRight, 
    Clock, 
    Eye, 
    HandCoins, 
    Landmark, 
    Lock, 
    PlusCircle, 
    Receipt, 
    Send, 
    Users, 
    Wallet 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';

const props = defineProps({
    account: Object,
    recentTransactions: Object,
    metrics: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Dashboard' }],
});

const { user } = useAuth();

const formatCurrency = (amountInCents) => {
    if (amountInCents === null || amountInCents === undefined) return '0.00';
    return (amountInCents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Account Balance Card & Quick Actions -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    
                    <!-- Wallet Info -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-10  h-10 flex items-center justify-center">
                                    <Wallet size="20" />
                                </div>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-base-content/70">
                                    Main Wallet Balance
                                </h2>
                                <p class="text-xs text-base-content/50">
                                    Account Slug: <span class="font-mono font-semibold">{{ account?.slug ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-baseline gap-2 pt-1">
                            <span class="text-3xl font-extrabold text-base-content">
                                {{ account?.cleared_balance?.formatted ?? '0.00' }}
                            </span>
                            <span class="text-sm font-semibold text-base-content/60">
                                {{ account?.currency ?? 'BDT' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-4 text-xs pt-2 text-base-content/70">
                            <div>
                                <span class="text-base-content/50">Available Balance:</span>
                                <span class="font-semibold ms-1">{{ account?.available_balance?.formatted ?? '0.00' }} {{ account?.currency ?? 'BDT' }}</span>
                            </div>
                            <div v-if="metrics?.total_held_amount > 0">
                                <span class="text-base-content/50">Active Holds:</span>
                                <span class="font-semibold text-warning ms-1">{{ formatCurrency(metrics?.total_held_amount) }} {{ account?.currency ?? 'BDT' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Buttons Grid -->
                    <div class="flex flex-wrap gap-3 items-center">
                        <Button as="link" :href="transfersIndex()" color="primary" size="sm">
                            <Send class="inline-block me-1" size="16" /> Transfer
                        </Button>
                        <Button as="link" :href="moneyRequestsIndex()" color="accent" size="sm">
                            <HandCoins class="inline-block me-1" size="16" /> Request
                        </Button>
                        <Button as="link" :href="billSplitsIndex()" color="info" size="sm">
                            <Users class="inline-block me-1" size="16" /> Bill Split
                        </Button>
                        <Button as="link" :href="depositsIndex()" color="secondary" size="sm">
                            <PlusCircle class="inline-block me-1" size="16" /> Deposit
                        </Button>
                        <Button as="link" :href="loansIndex()" color="neutral" size="sm">
                            <Landmark class="inline-block me-1" size="16" /> Loans
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary / Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Pending Money Requests -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Pending Requests
                        </p>
                        <p class="text-2xl font-bold text-primary mt-1">
                            {{ metrics?.pending_requests_count ?? 0 }}
                        </p>
                        <Button as="link" :href="moneyRequestsIndex()" color="primary" soft size="xs" class="mt-2">
                            View Requests
                        </Button>
                    </div>
                    <div class="p-3 bg-primary/10 text-primary rounded-xl">
                        <Clock size="24" />
                    </div>
                </div>
            </div>

            <!-- Active Borrowed Loans -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Loans Borrowed
                        </p>
                        <p class="text-2xl font-bold text-warning mt-1">
                            {{ formatCurrency(metrics?.loans_borrowed_total) }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <Button as="link" :href="loansIndex({ query: { tab: 'taken' } })" color="warning" soft size="xs" class="mt-2">
                            Pay Back
                        </Button>
                    </div>
                    <div class="p-3 bg-warning/10 text-warning rounded-xl">
                        <ArrowDownLeft size="24" />
                    </div>
                </div>
            </div>

            <!-- Active Lent Loans -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Loans Lent
                        </p>
                        <p class="text-2xl font-bold text-success mt-1">
                            {{ formatCurrency(metrics?.loans_lent_total) }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <Button as="link" :href="loansIndex({ query: { tab: 'given' } })" color="success" soft size="xs" class="mt-2">
                            View Given
                        </Button>
                    </div>
                    <div class="p-3 bg-success/10 text-success rounded-xl">
                        <ArrowUpRight size="24" />
                    </div>
                </div>
            </div>

            <!-- Total Held Funds -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Reserved Holds
                        </p>
                        <p class="text-2xl font-bold text-secondary mt-1">
                            {{ formatCurrency(metrics?.total_held_amount) }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-2">Active balance holds</p>
                    </div>
                    <div class="p-3 bg-secondary/10 text-secondary rounded-xl">
                        <Lock size="24" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table Section (Deferred) -->
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-bold">Recent Transactions</h3>
                        <p class="text-xs text-base-content/60">Your latest account activities and ledger updates</p>
                    </div>
                    <Button as="link" :href="transactionsIndex()" color="primary" soft size="sm">
                        View All
                    </Button>
                </div>

                <Deferred data="recentTransactions">
                    <template #fallback>
                        <div class="space-y-3 py-4">
                            <div class="skeleton h-12 w-full bg-base-200"></div>
                            <div class="skeleton h-12 w-full bg-base-200"></div>
                            <div class="skeleton h-12 w-full bg-base-200"></div>
                        </div>
                    </template>

                    <div v-if="!recentTransactions?.data?.length" class="text-center py-10">
                        <Receipt class="size-12 mx-auto text-base-content/30" />
                        <p class="mt-2 text-sm text-base-content/60 italic">No recent transactions found.</p>
                    </div>

                    <div v-else class="overflow-x-auto shadow-md rounded-md">
                        <DataTable :value="recentTransactions.data" tableStyle="min-width: 50rem" class="bg-base-100">
                            <Column field="reference" header="Reference">
                                <template #body="slotProps">
                                    <span class="font-mono text-xs font-semibold">
                                        {{ slotProps.data.reference }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="type" header="Type">
                                <template #body="slotProps">
                                    <span class="badge badge-sm capitalize" :class="{
                                        'badge-primary': slotProps.data.type === 'transfer',
                                        'badge-success': slotProps.data.type === 'deposit',
                                        'badge-warning': slotProps.data.type === 'money_request',
                                        'badge-info': slotProps.data.type === 'loan',
                                        'badge-neutral': !['transfer', 'deposit', 'money_request', 'loan'].includes(slotProps.data.type)
                                    }">
                                        {{ slotProps.data.type?.replace('_', ' ') }}
                                    </span>
                                </template>
                            </Column>
                            <Column header="Initiator">
                                <template #body="slotProps">
                                    <span class="text-sm font-medium">
                                        {{ slotProps.data.initiator?.name || 'System' }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="created_at" header="Date">
                                <template #body="slotProps">
                                    <span class="text-xs text-base-content/70">
                                        {{ slotProps.data.created_at?.formatted }}
                                    </span>
                                </template>
                            </Column>
                            <Column header="View">
                                <template #body="slotProps">
                                    <Button as="link" :href="transactionsShow({ transaction: slotProps.data.id })" color="secondary" size="sm">
                                        <Eye class="inline-block" size="16" />
                                    </Button>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </Deferred>
            </div>
        </div>

    </div>
</template>

