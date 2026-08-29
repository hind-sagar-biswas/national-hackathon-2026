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
    EyeOff,
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
import { computed, ref } from 'vue';

const props = defineProps({
    account: Object,
    recentTransactions: Object,
    metrics: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Dashboard' }],
});

const { user } = useAuth();
const showCardNumber = ref(false);

// Real 3D Cursor Tilt & Glare State
const cardRef = ref(null);
const cardTransform = ref('perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)');
const shineStyle = ref({
    background: 'radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%)',
});

const handleMouseMove = (e) => {
    if (!cardRef.value) return;
    const rect = cardRef.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    // Smooth real 3D tilt calculation based on cursor offset
    const rotateX = ((centerY - y) / centerY) * 16;
    const rotateY = ((x - centerX) / centerX) * 16;

    cardTransform.value = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale3d(1.03, 1.03, 1.03)`;

    // Cursor-following light glare spot
    const shineX = (x / rect.width) * 100;
    const shineY = (y / rect.height) * 100;
    shineStyle.value = {
        background: `radial-gradient(circle at ${shineX.toFixed(1)}% ${shineY.toFixed(1)}%, rgba(255, 255, 255, 0.28) 0%, rgba(255, 255, 255, 0) 65%)`,
    };
};

const handleMouseLeave = () => {
    cardTransform.value = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    shineStyle.value = {
        background: 'radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%)',
    };
};

const formatCurrency = (amountInCents) => {
    if (amountInCents === null || amountInCents === undefined) return '0.00';
    return (amountInCents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Deterministic 16-digit card number starting with 201 for each user
const fullCardNumber = computed(() => {
    const id = user.value?.id || props.account?.user_id || 1;
    const s1 = String((id * 9301 + 49297) % 9000 + 1000);
    const s2 = String((id * 49297 + 9301) % 9000 + 1000);
    const s3 = String((id * 71253 + 13579) % 9000 + 1000);
    return `201${s1.substring(1)} ${s2} ${s3} ${String((id * 31415 + 27182) % 9000 + 1000)}`;
});

// Hidden mode showing 4 digits at front and 2 digits at back (e.g. 2010 •••• •••• ••47)
const maskedCardNumber = computed(() => {
    const full = fullCardNumber.value;
    const digits = full.replace(/\s+/g, '');
    const front4 = digits.substring(0, 4);
    const last2 = digits.substring(14, 16);
    return `${front4} •••• •••• ••${last2}`;
});
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Account Balance Card & Cursor-Driven 3D Shiny Debit Card Grid -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6">
                <div class="flex flex-col xl:flex-row items-center justify-between gap-8">
                    
                    <!-- Wallet Info & Quick Actions -->
                    <div class="space-y-4 flex-1">
                        <div class="flex items-center gap-3">
                            <span class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center">
                                    <Wallet size="24" />
                                </div>
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-base-content">
                                    Main Wallet & Account Overview
                                </h2>
                                <p class="text-xs text-base-content/60">
                                    Account Slug: <span class="font-mono font-bold text-primary">{{ account?.slug ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-baseline gap-2 pt-1">
                            <span class="text-4xl font-black text-base-content">
                                {{ account?.cleared_balance?.formatted ?? '0.00' }}
                            </span>
                            <span class="text-base font-bold text-base-content/60">
                                {{ account?.currency ?? 'BDT' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-4 text-xs text-base-content/70">
                            <div class="bg-base-100 px-3 py-1.5 rounded-lg border border-base-300">
                                <span class="text-base-content/60">Available Balance:</span>
                                <span class="font-bold ms-1 text-base-content">{{ account?.available_balance?.formatted ?? '0.00' }} {{ account?.currency ?? 'BDT' }}</span>
                            </div>
                            <div v-if="metrics?.total_held_amount > 0" class="bg-warning/10 text-warning border border-warning/20 px-3 py-1.5 rounded-lg">
                                <span class="opacity-80">Active Holds:</span>
                                <span class="font-bold ms-1">{{ formatCurrency(metrics?.total_held_amount) }} {{ account?.currency ?? 'BDT' }}</span>
                            </div>
                        </div>

                        <!-- Quick Actions Buttons Grid -->
                        <div class="flex flex-wrap gap-2.5 items-center pt-2">
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

                    <!-- Real Dynamic 3D Cursor Tilt Black Shiny Card -->
                    <div class="flex-shrink-0 py-2 px-2">
                        <div 
                            ref="cardRef"
                            @mousemove="handleMouseMove"
                            @mouseleave="handleMouseLeave"
                            :style="{ transform: cardTransform }"
                            class="relative w-80 sm:w-96 rounded-2xl p-6 bg-black text-white shadow-[0_25px_50px_-12px_rgba(0,0,0,0.85)] border border-white/15 overflow-hidden transition-transform duration-100 ease-out cursor-pointer select-none bg-[radial-gradient(circle_at_bottom_left,#ffffff0c_35%,transparent_36%),radial-gradient(circle_at_top_right,#ffffff0c_35%,transparent_36%)] bg-[length:4.95em_4.95em]"
                        >
                            <!-- Dynamic Cursor Light Glare Reflection -->
                            <div 
                                class="absolute inset-0 pointer-events-none transition-all duration-75"
                                :style="shineStyle"
                            ></div>

                            <!-- Metallic Glossy Sheen Overlay -->
                            <div class="absolute inset-0 bg-linear-to-tr from-white/10 via-transparent to-white/5 pointer-events-none"></div>

                            <div class="relative z-10 space-y-4">
                                <!-- Card Header -->
                                <div class="flex justify-between items-center">
                                    <div class="font-bold tracking-widest text-xs uppercase opacity-90 flex items-center gap-2">
                                        <span class="inline-block size-2 rounded-full bg-amber-400 animate-pulse"></span>
                                        BANK OF LATVERIA
                                    </div>
                                    <div class="text-3xl opacity-20 font-serif">❁</div>
                                </div>

                                <!-- Golden SIM / EMV Smart Chip -->
                                <div class="w-11 h-8 rounded-md bg-linear-to-tr from-amber-400 via-amber-300 to-yellow-500 border border-yellow-200/50 shadow-inner relative flex items-center justify-center overflow-hidden my-1">
                                    <div class="absolute inset-x-0 h-[1px] bg-amber-800/40"></div>
                                    <div class="absolute inset-y-0 w-[1px] bg-amber-800/40"></div>
                                    <div class="size-4 border border-amber-800/40 rounded-xs"></div>
                                </div>

                                <!-- Card Number & Eye Toggle Button -->
                                <div class="space-y-1">
                                    <span class="text-[9px] uppercase tracking-wider opacity-40 font-bold block">CARD NUMBER</span>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-base sm:text-lg font-mono tracking-wider font-bold opacity-95">
                                            {{ showCardNumber ? fullCardNumber : maskedCardNumber }}
                                        </span>

                                        <button 
                                            type="button" 
                                            @click.stop="showCardNumber = !showCardNumber" 
                                            class="p-1.5 rounded-md bg-white/10 hover:bg-white/20 text-white/70 hover:text-white transition-colors focus:outline-none z-20"
                                            :title="showCardNumber ? 'Hide Card Number' : 'Show Card Number'"
                                        >
                                            <EyeOff v-if="showCardNumber" class="size-4 text-warning" />
                                            <Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Footer: Holder, Valid From & Valid Till -->
                                <div class="flex justify-between items-end pt-2 text-xs">
                                    <div>
                                        <div class="text-[9px] uppercase tracking-wider opacity-40 font-bold">CARD HOLDER</div>
                                        <div class="text-sm font-bold tracking-wide uppercase text-white/90">
                                            {{ user?.name || 'VALUED CUSTOMER' }}
                                        </div>
                                    </div>

                                    <div class="flex gap-3 text-right">
                                        <div>
                                            <div class="text-[9px] uppercase tracking-wider opacity-40 font-bold">VALID FROM</div>
                                            <div class="text-xs font-mono font-bold text-white/90">08/24</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] uppercase tracking-wider opacity-40 font-bold">VALID TILL</div>
                                            <div class="text-xs font-mono font-bold text-white/90">08/29</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
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
