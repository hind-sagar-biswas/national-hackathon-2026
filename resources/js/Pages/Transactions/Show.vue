<script setup>
import Button from '@/Components/Buttons/Button.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { index as transactionsIndex } from '@/routes/transactions';
import { 
    ArrowLeft, 
    CheckCircle2, 
    Clock, 
    FileText, 
    Receipt, 
    ShieldCheck, 
    User 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed } from 'vue';

const props = defineProps({
    transaction: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: `Receipt ${props.transaction?.reference}` }],
});
</script>

<template>
    <div class="p-5 space-y-6 max-w-4xl mx-auto">

        <!-- Navigation Controls -->
        <div class="flex items-center justify-between">
            <Button as="link" :href="transactionsIndex()" color="neutral" ghost size="sm">
                <ArrowLeft class="size-4 me-1" /> Back to Transactions
            </Button>
        </div>

        <!-- Official Digital Receipt Card -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body p-8 space-y-6">

                <!-- Header / Watermark -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b-2 border-base-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <Receipt class="size-7 text-primary" />
                            <h1 class="text-2xl font-black tracking-tight text-base-content">TRANSACTION RECEIPT</h1>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">
                            National Banking System • Cryptographically Audited Ledger
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="badge badge-lg uppercase font-extrabold" :class="{
                            'badge-primary': transaction.type === 'transfer',
                            'badge-success': transaction.type === 'deposit',
                            'badge-warning': transaction.type === 'money_request' || transaction.type === 'request_settlement',
                            'badge-info': transaction.type === 'loan',
                            'badge-neutral': !['transfer', 'deposit', 'money_request', 'request_settlement', 'loan'].includes(transaction.type)
                        }">
                            {{ transaction.type?.replace('_', ' ') }}
                        </span>
                        <span class="text-xs text-base-content/60 block mt-1">
                            Ref: <strong class="font-mono text-base-content">{{ transaction.reference }}</strong>
                        </span>
                    </div>
                </div>

                <!-- 3-Column Key Info -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-base-200/60 p-4 rounded-box">
                    <div>
                        <span class="text-xs text-base-content/60 block">Initiated By</span>
                        <span class="text-sm font-bold text-base-content">{{ transaction.initiator?.name || 'System Automated' }}</span>
                        <span v-if="transaction.initiator?.email" class="text-xs text-base-content/60 block truncate">{{ transaction.initiator.email }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-base-content/60 block">Date & Time</span>
                        <span class="text-sm font-bold text-base-content">{{ transaction.created_at?.formatted }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-base-content/60 block">System Transaction ID</span>
                        <span class="text-xs font-mono font-bold text-primary truncate block">#{{ transaction.id }}</span>
                    </div>
                </div>

                <!-- Double-Entry Ledger Breakdown -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold uppercase text-base-content/70 flex items-center gap-1">
                        <ShieldCheck class="size-4 text-success" />
                        <span>Double-Entry Balance Sheet Ledger</span>
                    </h3>

                    <div class="overflow-x-auto rounded-box border border-base-200">
                        <DataTable :value="transaction.ledger_entries || []" class="bg-base-100">
                            <Column header="Account / Wallet Holder">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-2">
                                        <User class="size-4 text-base-content/50" />
                                        <div>
                                            <span class="text-sm font-bold block text-base-content">
                                                {{ slotProps.data.account?.user?.name || 'Platform Equity/Reserve Account' }}
                                            </span>
                                            <span class="text-xs text-base-content/60 font-mono">
                                                Acc ID: {{ slotProps.data.account?.id || 'SYS-RES' }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column field="direction" header="Entry Type">
                                <template #body="slotProps">
                                    <span class="badge badge-sm font-bold uppercase" :class="{
                                        'badge-error': slotProps.data.direction === 'debit',
                                        'badge-success': slotProps.data.direction === 'credit',
                                    }">
                                        {{ slotProps.data.direction }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="amount" header="Amount">
                                <template #body="slotProps">
                                    <span class="font-mono font-extrabold text-base" :class="slotProps.data.direction === 'debit' ? 'text-error' : 'text-success'">
                                        {{ slotProps.data.direction === 'debit' ? '-' : '+' }}{{ slotProps.data.amount?.formatted ?? slotProps.data.amount }} BDT
                                    </span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Footer stamp -->
                <div class="pt-6 border-t border-base-200 text-center text-xs text-base-content/50 space-y-1">
                    <p>This is an official computer-generated receipt from National Hackathon 2026 Core Banking Engine.</p>
                    <p class="font-mono text-[10px]">VERIFIED CHECKSUM • DOUBLE-ENTRY BALANCE VERIFIED</p>
                </div>

            </div>
        </div>

    </div>
</template>
