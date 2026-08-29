<script setup>
import Button from '@/Components/Buttons/Button.vue';
import FormField from '@/Components/Forms/FormField.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import SearchBox from '@/Components/Forms/SearchBox.vue';
import SelectBox from '@/Components/Forms/SelectBox.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import { useAuth, useFilter } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { confirm as confirmDeposit, index, reject as rejectDeposit } from '@/routes/admin/deposits';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { 
    CheckCircle, 
    Clock, 
    Download, 
    ShieldCheck, 
    User, 
    Wallet, 
    XCircle 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, ref } from 'vue';

const props = defineProps({
    list: Object,
    filters: Object,
    providerOptions: Array,
    statusOptions: Array,
    counts: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Admin - Deposit Approval Queue' }],
});

const { can } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});

// Reject modal state
const showRejectModal = ref(false);
const selectedDepositToReject = ref(null);

const rejectForm = useForm({
    reason: '',
});

const handleConfirm = (deposit) => {
    if (confirm(`Approve deposit request #${deposit.id} for ${deposit.amount?.formatted ?? deposit.amount} BDT and credit user wallet?`)) {
        router.post(confirmDeposit({ depositRequest: deposit.id }), {}, { preserveScroll: true });
    }
};

const openRejectModal = (deposit) => {
    selectedDepositToReject.value = deposit;
    rejectForm.reset();
    showRejectModal.value = true;
};

const handleRejectSubmit = () => {
    if (!selectedDepositToReject.value) return;

    rejectForm.post(rejectDeposit({ depositRequest: selectedDepositToReject.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Header Card & Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card bg-base-200 shadow-sm border border-base-300 md:col-span-2">
                <div class="card-body p-6 flex flex-row items-center gap-3">
                    <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <Wallet size="24" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-content">
                            Deposit Verification Queue
                        </h1>
                        <p class="text-xs text-base-content/60">
                            Review and authorize pending user deposit requests.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Card 1 -->
            <div class="card bg-warning/10 border border-warning/30 shadow-sm">
                <div class="card-body p-4 flex flex-row items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-warning-content block">Pending Approval</span>
                        <span class="text-2xl font-black text-warning">{{ counts?.pending ?? 0 }}</span>
                    </div>
                    <Clock class="size-8 text-warning opacity-70" />
                </div>
            </div>

            <!-- Stats Card 2 -->
            <div class="card bg-success/10 border border-success/30 shadow-sm">
                <div class="card-body p-4 flex flex-row items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-success-content block">Confirmed</span>
                        <span class="text-2xl font-black text-success">{{ counts?.confirmed ?? 0 }}</span>
                    </div>
                    <CheckCircle class="size-8 text-success opacity-70" />
                </div>
            </div>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-3"
                    placeholder="Search by Trx ID, User Name, Email..." />
                
                <SelectBox v-model="filters.provider" placeholder="All Gateways" class="col-span-1 md:col-span-2"
                    :options="providerOptions" />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="statusOptions" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Admin Deposits DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 55rem" class="bg-base-100">
                    <Column header="User / Account">
                        <template #body="slotProps">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                        <span class="text-xs font-bold">{{ slotProps.data.user?.name?.charAt(0) || 'U' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-bold block text-base-content">{{ slotProps.data.user?.name || 'Unknown User' }}</span>
                                    <span class="text-xs text-base-content/60 block">{{ slotProps.data.user?.email }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="provider" header="Gateway">
                        <template #body="slotProps">
                            <span class="badge badge-outline uppercase font-bold">
                                {{ slotProps.data.provider }}
                            </span>
                        </template>
                    </Column>
                    <Column field="provider_ref" header="Transaction Ref / ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-primary">
                                {{ slotProps.data.provider_ref }}
                            </span>
                        </template>
                    </Column>
                    <Column field="amount" header="Amount">
                        <template #body="slotProps">
                            <span class="font-extrabold text-base-content">
                                {{ slotProps.data.amount?.formatted ?? slotProps.data.amount }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold gap-1" :class="{
                                'badge-warning': slotProps.data.status === 'pending',
                                'badge-success': slotProps.data.status === 'confirmed',
                                'badge-error': ['rejected', 'failed'].includes(slotProps.data.status),
                            }">
                                {{ slotProps.data.status }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Requested Date">
                        <template #body="slotProps">
                            <span class="text-xs text-base-content/70">
                                {{ slotProps.data.created_at?.formatted }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex items-center gap-1" v-if="slotProps.data.status === 'pending'">
                                <Button 
                                    v-if="can('confirm-deposits')" 
                                    color="success" 
                                    size="sm" 
                                    @click="handleConfirm(slotProps.data)"
                                    title="Confirm & Credit Wallet"
                                >
                                    Confirm
                                </Button>

                                <Button 
                                    v-if="can('reject-deposits')" 
                                    color="error" 
                                    soft 
                                    size="sm" 
                                    @click="openRejectModal(slotProps.data)"
                                    title="Reject Deposit"
                                >
                                    Reject
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

        <!-- Reject Deposit Modal -->
        <DialogModal :show="showRejectModal" @close="showRejectModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-error">
                    <XCircle class="size-5" />
                    <span>Reject Deposit Request #{{ selectedDepositToReject?.id }}</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleRejectSubmit" id="reject-deposit-form" class="space-y-4">
                    <div class="bg-base-200 p-3 rounded-md text-xs space-y-1">
                        <p><strong class="text-base-content">User:</strong> {{ selectedDepositToReject?.user?.name }} ({{ selectedDepositToReject?.user?.email }})</p>
                        <p><strong class="text-base-content">Amount:</strong> {{ selectedDepositToReject?.amount?.formatted }} BDT</p>
                        <p><strong class="text-base-content">Ref:</strong> <span class="font-mono">{{ selectedDepositToReject?.provider_ref }}</span></p>
                    </div>

                    <div>
                        <InputLabel for="reason" value="Reason for Rejection" />
                        <TextInput 
                            id="reason" 
                            type="text" 
                            v-model="rejectForm.reason" 
                            class="w-full mt-1"
                            placeholder="e.g. Invalid Transaction ID, Fake reference" 
                            required 
                        />
                        <InputError :message="rejectForm.errors.reason" class="mt-1" />
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showRejectModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    color="error" 
                    :disabled="rejectForm.processing" 
                    type="submit" 
                    form="reject-deposit-form"
                >
                    Confirm Rejection
                </Button>
            </template>
        </DialogModal>

    </div>
</template>
