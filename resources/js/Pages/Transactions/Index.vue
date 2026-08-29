<script setup>
import Button from '@/Components/Buttons/Button.vue';
import SearchBox from '@/Components/Forms/SearchBox.vue';
import SelectBox from '@/Components/Forms/SelectBox.vue';
import { useAuth, useFilter } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { index, show as transactionsShow } from '@/routes/transactions';
import { Eye, Receipt, Search } from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';

const props = defineProps({
    list: Object,
    filters: Object,
    typeOptions: Array,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Transactions Audit' }],
});

const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Header Card -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6">
                <div class="flex items-center gap-3">
                    <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center">
                        <Receipt size="24" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-content">
                            Double-Entry Ledger Transactions
                        </h1>
                        <p class="text-xs text-base-content/60">
                            View audit trail, system transaction references, and digital receipts.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-4"
                    placeholder="Search by Transaction Ref or ID..." />
                
                <SelectBox v-model="filters.type" placeholder="All Transaction Types" class="col-span-1 md:col-span-2"
                    :options="typeOptions" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Transactions DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 55rem" class="bg-base-100">
                    <Column field="reference" header="Reference ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-primary">
                                {{ slotProps.data.reference }}
                            </span>
                        </template>
                    </Column>
                    <Column field="type" header="Transaction Type">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold" :class="{
                                'badge-primary': slotProps.data.type === 'transfer',
                                'badge-success': slotProps.data.type === 'deposit',
                                'badge-warning': slotProps.data.type === 'money_request' || slotProps.data.type === 'request_settlement',
                                'badge-info': slotProps.data.type === 'loan',
                                'badge-neutral': !['transfer', 'deposit', 'money_request', 'request_settlement', 'loan'].includes(slotProps.data.type)
                            }">
                                {{ slotProps.data.type?.replace('_', ' ') }}
                            </span>
                        </template>
                    </Column>
                    <Column field="user_direction" header="Direction">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.user_direction" class="badge badge-sm font-bold uppercase" :class="{
                                'badge-error': slotProps.data.user_direction === 'debit',
                                'badge-success': slotProps.data.user_direction === 'credit',
                            }">
                                {{ slotProps.data.user_direction === 'debit' ? 'Debit (-)' : 'Credit (+)' }}
                            </span>
                            <span v-else class="text-xs text-base-content/50">—</span>
                        </template>
                    </Column>
                    <Column field="amount" header="Amount">
                        <template #body="slotProps">
                            <span class="font-extrabold" :class="{
                                'text-error': slotProps.data.user_direction === 'debit',
                                'text-success': slotProps.data.user_direction === 'credit',
                                'text-base-content': !slotProps.data.user_direction,
                            }">
                                {{ slotProps.data.user_direction === 'debit' ? '-' : (slotProps.data.user_direction === 'credit' ? '+' : '') }}
                                {{ slotProps.data.amount?.formatted ?? '0.00' }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column header="Initiated By">
                        <template #body="slotProps">
                            <span class="text-sm font-medium">
                                {{ slotProps.data.initiator?.name || 'System Auto' }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Date & Time">
                        <template #body="slotProps">
                            <span class="text-xs text-base-content/70">
                                {{ slotProps.data.created_at?.formatted }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Digital Receipt">
                        <template #body="slotProps">
                            <Button as="link" :href="transactionsShow({ transaction: slotProps.data.id })" color="secondary" size="sm">
                                <Eye class="size-4 me-1" /> View Receipt
                            </Button>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

    </div>
</template>
