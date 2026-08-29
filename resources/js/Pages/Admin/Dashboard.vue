<script setup>
import Button from '@/Components/Buttons/Button.vue';
import { useAuth } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { index as depositsIndex } from '@/routes/deposits';
import { index as usersIndex } from '@/routes/users';
import { Deferred } from '@inertiajs/vue3';
import { 
    Activity, 
    Banknote, 
    Clock, 
    Landmark, 
    Lock, 
    PiggyBank, 
    ShieldCheck, 
    Users 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';

const props = defineProps({
    systemAccounts: Object,
    metrics: Object,
    recentOperations: Object,
    pendingDeposits: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Admin Dashboard' }],
});

const { user } = useAuth();

const formatCurrency = (amountInCents) => {
    if (amountInCents === null || amountInCents === undefined) return '0.00';
    return (amountInCents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Header Title -->
       <div class="card bg-base-200 shadow-sm border border-base-300">
    <div class="card-body p-6">
        <div class="flex items-center gap-3">

            <div class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                <ShieldCheck size="20" />
            </div>

            <div>
                <h1 class="text-xl font-bold text-base-content">
                    Admin Control Panel
                </h1>

                <p class="text-xs text-base-content/60">
                    Overview of system reserves, platform equity, user liabilities, and ledger operations.
                </p>
            </div>
        </div>
    </div>
</div>

        <!-- System Account Balances Overview Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Platform Equity -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Platform Equity
                        </p>
                        <p class="text-2xl font-bold text-primary mt-1">
                            {{ systemAccounts?.platform_equity?.cleared_balance?.formatted ?? '0.00' }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-2">System capital & reserves</p>
                    </div>
                    <div class="p-3 bg-primary/10 text-primary rounded-xl">
                        <PiggyBank size="24" />
                    </div>
                </div>
            </div>

            <!-- Fee Income -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Fee Income
                        </p>
                        <p class="text-2xl font-bold text-secondary mt-1">
                            {{ systemAccounts?.fee_income?.cleared_balance?.formatted ?? '0.00' }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-2">Accumulated fee revenue</p>
                    </div>
                    <div class="p-3 bg-secondary/10 text-secondary rounded-xl">
                        <Banknote size="24" />
                    </div>
                </div>
            </div>

            <!-- Cash Reserve -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            Cash Reserve
                        </p>
                        <p class="text-2xl font-bold text-accent mt-1">
                            {{ systemAccounts?.cash_reserve?.cleared_balance?.formatted ?? '0.00' }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-2">System liquid reserves</p>
                    </div>
                    <div class="p-3 bg-accent/10 text-accent rounded-xl">
                        <Landmark size="24" />
                    </div>
                </div>
            </div>

            <!-- Total User Liabilities -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">
                            User Liabilities
                        </p>
                        <p class="text-2xl font-bold text-info mt-1">
                            {{ formatCurrency(systemAccounts?.total_user_liabilities) }} <span class="text-xs font-normal">BDT</span>
                        </p>
                        <p class="text-xs text-base-content/50 mt-2">Total user wallet balances</p>
                    </div>
                    <div class="p-3 bg-info/10 text-info rounded-xl">
                        <Users size="24" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">Total Users</p>
                        <p class="text-2xl font-bold text-primary mt-1">{{ metrics?.total_users_count ?? 0 }}</p>
                        <Button as="link" :href="usersIndex()" color="primary" soft size="xs" class="mt-2">
                            Manage Users
                        </Button>
                    </div>
                    <div class="p-3 bg-primary/10 text-primary rounded-xl">
                        <Users size="24" />
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">Pending Deposits</p>
                        <p class="text-2xl font-bold text-warning mt-1">{{ metrics?.pending_deposits_count ?? 0 }}</p>
                        <Button as="link" :href="depositsIndex()" color="warning" soft size="xs" class="mt-2">
                            Verify Deposits
                        </Button>
                    </div>
                    <div class="p-3 bg-warning/10 text-warning rounded-xl">
                        <Clock size="24" />
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">Active Holds</p>
                        <p class="text-2xl font-bold text-secondary mt-1">{{ metrics?.active_holds_count ?? 0 }}</p>
                        <p class="text-xs text-base-content/50 mt-2">{{ formatCurrency(metrics?.total_held_amount) }} BDT</p>
                    </div>
                    <div class="p-3 bg-secondary/10 text-secondary rounded-xl">
                        <Lock size="24" />
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5 flex flex-row items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-base-content/60 uppercase">Active Loans</p>
                        <p class="text-2xl font-bold text-accent mt-1">{{ formatCurrency(metrics?.total_active_loans) }} <span class="text-xs font-normal">BDT</span></p>
                        <p class="text-xs text-base-content/50 mt-2">Active P2P loans</p>
                    </div>
                    <div class="p-3 bg-accent/10 text-accent rounded-xl">
                        <Landmark size="24" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section: Recent Operations & Pending Deposits -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Recent System Operations -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Activity size="20" class="text-primary" />
                        <h3 class="text-base font-bold text-base-content">Recent Operation Events</h3>
                    </div>

                    <Deferred data="recentOperations">
                        <template #fallback>
                            <div class="space-y-3 py-4">
                                <div class="skeleton h-10 w-full bg-base-200"></div>
                                <div class="skeleton h-10 w-full bg-base-200"></div>
                            </div>
                        </template>

                        <div v-if="!recentOperations?.data?.length" class="text-center py-8">
                            <p class="text-xs text-base-content/60 italic">No recent operation events found.</p>
                        </div>

                        <div v-else class="overflow-x-auto shadow-md rounded-md">
                            <DataTable :value="recentOperations.data" class="bg-base-100">
                                <Column field="event_type" header="Event Type">
                                    <template #body="slotProps">
                                        <span class="font-semibold text-xs">
                                            {{ slotProps.data.event_type }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="amount" header="Amount">
                                    <template #body="slotProps">
                                        <span class="font-mono text-xs font-semibold">
                                            {{ formatCurrency(slotProps.data.amount) }} BDT
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
                            </DataTable>
                        </div>
                    </Deferred>
                </div>
            </div>

            <!-- Pending Deposits Queue -->
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <Clock size="20" class="text-warning" />
                            <h3 class="text-base font-bold text-base-content">Pending Deposits Queue</h3>
                        </div>
                        <Button as="link" :href="depositsIndex()" color="warning" soft size="xs">
                            View All
                        </Button>
                    </div>

                    <Deferred data="pendingDeposits">
                        <template #fallback>
                            <div class="space-y-3 py-4">
                                <div class="skeleton h-10 w-full bg-base-200"></div>
                                <div class="skeleton h-10 w-full bg-base-200"></div>
                            </div>
                        </template>

                        <div v-if="!pendingDeposits?.data?.length" class="text-center py-8">
                            <p class="text-xs text-base-content/60 italic">No pending deposit requests to verify.</p>
                        </div>

                        <div v-else class="overflow-x-auto shadow-md rounded-md">
                            <DataTable :value="pendingDeposits.data" class="bg-base-100">
                                <Column header="User">
                                    <template #body="slotProps">
                                        <span class="text-xs font-medium">
                                            {{ slotProps.data.user?.name }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="provider" header="Provider">
                                    <template #body="slotProps">
                                        <span class="badge badge-sm badge-warning uppercase font-bold">
                                            {{ slotProps.data.provider }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="provider_ref" header="Ref">
                                    <template #body="slotProps">
                                        <span class="font-mono text-xs">
                                            {{ slotProps.data.provider_ref }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="amount" header="Amount">
                                    <template #body="slotProps">
                                        <span class="font-semibold text-xs">
                                            {{ formatCurrency(slotProps.data.amount) }} BDT
                                        </span>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </Deferred>
                </div>
            </div>
        </div>

    </div>
</template>
