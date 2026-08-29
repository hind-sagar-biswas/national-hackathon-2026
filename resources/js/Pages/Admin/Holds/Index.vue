<script setup>
import Button from '@/Components/Buttons/Button.vue';
import SearchBox from '@/Components/Forms/SearchBox.vue';
import SelectBox from '@/Components/Forms/SelectBox.vue';
import { useAuth, useFilter } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { index, release as releaseHold } from '@/routes/admin/holds';
import { router, usePage } from '@inertiajs/vue3';
import { 
    AlertTriangle, 
    CheckCircle, 
    Clock, 
    Lock, 
    ShieldAlert, 
    Unlock, 
    User 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';

const props = defineProps({
    list: Object,
    filters: Object,
    statusOptions: Array,
    metrics: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Admin - Risk Holds Oversight' }],
});

const { can } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});

const handleRelease = (hold) => {
    if (confirm(`Release active hold #${hold.id} (${hold.amount?.formatted ?? hold.amount} BDT) back to the user's wallet balance?`)) {
        router.post(releaseHold({ hold: hold.id }), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Header & Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card bg-base-200 shadow-sm border border-base-300 sm:col-span-2">
                <div class="card-body p-6 flex flex-row items-center gap-3">
                    <div class="bg-warning text-warning-content rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <ShieldAlert size="24" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-content">
                            Risk Holds & Pre-Reservations
                        </h1>
                        <p class="text-xs text-base-content/60">
                            Monitor and release funds held by automated compliance rules.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Held Total Card -->
            <div class="card bg-warning/10 border border-warning/30 shadow-sm">
                <div class="card-body p-4 flex flex-row items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-warning-content block">Total Active Held</span>
                        <span class="text-xl font-black text-warning">{{ metrics?.total_held_active?.formatted ?? metrics?.total_held_active ?? 0 }} BDT</span>
                    </div>
                    <Lock class="size-7 text-warning opacity-70" />
                </div>
            </div>

            <!-- Active Holds Count Card -->
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-4 flex flex-row items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-base-content/70 block">Active Count</span>
                        <span class="text-xl font-extrabold text-base-content">{{ metrics?.active_count ?? 0 }}</span>
                    </div>
                    <Clock class="size-7 text-base-content opacity-50" />
                </div>
            </div>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-4"
                    placeholder="Search by reason, user name, email..." />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="statusOptions" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Holds DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 55rem" class="bg-base-100">
                    <Column field="id" header="Hold ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-warning">
                                #{{ slotProps.data.id }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Account / User">
                        <template #body="slotProps">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                        <span class="text-xs font-bold">{{ slotProps.data.account?.user?.name?.charAt(0) || 'U' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-bold block text-base-content">{{ slotProps.data.account?.user?.name || 'User Account' }}</span>
                                    <span class="text-xs text-base-content/60 block font-mono">{{ slotProps.data.account?.user?.email }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="amount" header="Held Amount">
                        <template #body="slotProps">
                            <span class="font-extrabold text-error">
                                {{ slotProps.data.amount?.formatted ?? slotProps.data.amount }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column field="reason" header="Hold Reason">
                        <template #body="slotProps">
                            <span class="text-xs text-base-content/80 font-medium italic">
                                {{ slotProps.data.reason || 'Automated Compliance Hold' }}
                            </span>
                        </template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold" :class="{
                                'badge-warning': slotProps.data.status === 'active',
                                'badge-success': slotProps.data.status === 'captured',
                                'badge-neutral': slotProps.data.status === 'released',
                            }">
                                {{ slotProps.data.status }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created Date">
                        <template #body="slotProps">
                            <span class="text-xs text-base-content/70">
                                {{ slotProps.data.created_at?.formatted }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <Button 
                                v-if="slotProps.data.status === 'active' && can('release-holds')" 
                                color="success" 
                                size="sm" 
                                @click="handleRelease(slotProps.data)"
                                title="Release Funds Back to Wallet"
                            >
                                <Unlock class="size-4 me-1" /> Release
                            </Button>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

    </div>
</template>
