<script setup>
import Button from '@/Components/Buttons/Button.vue';
import FormField from '@/Components/Forms/FormField.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import SearchBox from '@/Components/Forms/SearchBox.vue';
import SelectBox from '@/Components/Forms/SelectBox.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import { useAuth, useFilter, useIdempotentForm } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { 
    approve, 
    destroy as cancelRequest, 
    index, 
    reject, 
    store 
} from '@/routes/money-requests';
import { router, usePage } from '@inertiajs/vue3';
import { 
    Check, 
    Clock, 
    HandCoins, 
    PlusCircle, 
    Send, 
    ShieldAlert, 
    UserCheck, 
    X 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    list: Object,
    filters: Object,
    tab: String,
    counts: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Money Requests' }],
});

const { user } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), {
    tab: props.tab || 'incoming',
    status: props.filters?.status || '',
    search: props.filters?.search || '',
}, {
    debounceMs: { search: 500 },
});

// Keep tab in sync with props
watch(
    () => props.tab,
    (newTab) => {
        if (newTab && filters.tab !== newTab) {
            filters.tab = newTab;
        }
    }
);

const showCreateModal = ref(false);

const { form, submit, resetKey } = useIdempotentForm({
    payer: '',
    amount: '',
    type: 'standard',
    expires_in_days: 3,
    due_at: '',
    note: '',
    pre_hold: false,
});

// Idempotent forms for Approve and Decline actions
const approveForm = useIdempotentForm();
const rejectForm = useIdempotentForm();

const handleCreateSubmit = () => {
    submit('post', store(), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const handleApprove = (reqItem) => {
    approveForm.resetKey();
    approveForm.submit('post', approve(reqItem.id), {
        preserveScroll: true,
    });
};

const handleReject = (reqItem) => {
    rejectForm.resetKey();
    rejectForm.submit('post', reject(reqItem.id), {
        preserveScroll: true,
    });
};

const handleCancel = (reqItem) => {
    router.delete(cancelRequest(reqItem.id), { preserveScroll: true });
};

const openCreateModal = () => {
    resetKey();
    form.reset();
    form.type = 'standard';
    form.expires_in_days = 3;
    showCreateModal.value = true;
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Header Card -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center">
                            <HandCoins size="24" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-base-content">
                                Money & Loan Requests
                            </h1>
                            <p class="text-xs text-base-content/60">
                                Send standard payment or loan requests to other users or approve incoming requests.
                            </p>
                        </div>
                    </div>

                    <Button color="primary" @click="openCreateModal">
                        <PlusCircle class="inline-block me-1" size="16" /> Create Request
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tab Controls (Incoming vs Outgoing) -->
        <div class="tabs tabs-lift">
            <button 
                class="tab text-sm font-bold gap-2" 
                :class="{ 'tab-active': filters.tab === 'incoming' }" 
                @click="filters.tab = 'incoming'"
            >
                <span>Incoming Requests</span>
                <span v-if="counts?.incoming_pending > 0" class="badge badge-warning badge-sm font-bold">
                    {{ counts.incoming_pending }}
                </span>
            </button>
            <button 
                class="tab text-sm font-bold gap-2" 
                :class="{ 'tab-active': filters.tab === 'outgoing' }" 
                @click="filters.tab = 'outgoing'"
            >
                <span>Outgoing Requests</span>
                <span v-if="counts?.outgoing_pending > 0" class="badge badge-neutral badge-sm font-bold">
                    {{ counts.outgoing_pending }}
                </span>
            </button>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-4"
                    placeholder="Search requests by ID..." />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="[
                        { label: 'Pending', value: 'pending' },
                        { label: 'Approved', value: 'approved' },
                        { label: 'Rejected', value: 'rejected' },
                        { label: 'Expired', value: 'expired' },
                    ]" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Money Requests DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 50rem" class="bg-base-100">
                    <Column field="id" header="Request ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-primary">
                                #{{ slotProps.data.id }}
                            </span>
                        </template>
                    </Column>
                    <Column field="type" header="Type">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold" :class="slotProps.data.type === 'loan' ? 'badge-info' : 'badge-neutral'">
                                {{ slotProps.data.type === 'loan' ? 'P2P Loan' : 'Standard' }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Participant">
                        <template #body="slotProps">
                            <span class="text-sm font-medium">
                                {{ filters.tab === 'incoming' ? (slotProps.data.requester_account?.user?.name || 'Requester') : (slotProps.data.payer_account?.user?.name || 'Payer') }}
                            </span>
                        </template>
                    </Column>
                    <Column field="amount" header="Requested Amount">
                        <template #body="slotProps">
                            <span class="font-extrabold text-base-content">
                                {{ slotProps.data.amount?.formatted ?? slotProps.data.amount }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold" :class="{
                                'badge-warning': slotProps.data.status === 'pending',
                                'badge-success': slotProps.data.status === 'approved',
                                'badge-error': slotProps.data.status === 'rejected',
                                'badge-neutral': slotProps.data.status === 'expired',
                            }">
                                {{ slotProps.data.status }}
                            </span>
                        </template>
                    </Column>
                    <Column field="expires_at" header="Expiry / Due Date">
                        <template #body="slotProps">
                            <div class="text-xs space-y-0.5">
                                <div><strong class="text-base-content/60">Expires:</strong> {{ slotProps.data.expires_at?.formatted || 'No Expiry' }}</div>
                                <div v-if="slotProps.data.due_at"><strong class="text-info">Due:</strong> {{ slotProps.data.due_at?.formatted }}</div>
                            </div>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex items-center gap-1">
                                <template v-if="filters.tab === 'incoming' && slotProps.data.status === 'pending'">
                                    <Button 
                                        color="success" 
                                        size="sm" 
                                        :disabled="approveForm.form.processing"
                                        @click="handleApprove(slotProps.data)" 
                                        title="Approve & Pay"
                                    >
                                        <Check class="size-4 me-1" /> Approve
                                    </Button>
                                    <Button 
                                        color="error" 
                                        soft 
                                        size="sm" 
                                        :disabled="rejectForm.form.processing"
                                        @click="handleReject(slotProps.data)" 
                                        title="Decline"
                                    >
                                        <X class="size-4 me-1" /> Decline
                                    </Button>
                                </template>

                                <template v-if="filters.tab === 'outgoing' && slotProps.data.status === 'pending'">
                                    <Button color="neutral" ghost size="sm" @click="handleCancel(slotProps.data)" title="Cancel Request">
                                        Cancel
                                    </Button>
                                </template>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

        <!-- Create Money Request Modal -->
        <DialogModal :show="showCreateModal" @close="showCreateModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-primary">
                    <HandCoins class="size-5" />
                    <span>Create Money or Loan Request</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleCreateSubmit" id="create-request-form" class="space-y-4">
                    <!-- Request Type -->
                    <div>
                        <InputLabel for="type" value="Request Purpose / Type" />
                        <SelectBox 
                            id="type"
                            v-model="form.type"
                            class="w-full mt-1"
                            :options="[
                                { label: 'Standard Payment Request', value: 'standard' },
                                { label: 'P2P Micro-Loan Request', value: 'loan' },
                            ]"
                        />
                        <InputError :message="form.errors.type" class="mt-1" />
                    </div>

                    <!-- Payer -->
                    <div>
                        <InputLabel for="payer" value="Payer / Lender (Email or Phone)" />
                        <TextInput 
                            id="payer" 
                            type="text" 
                            v-model="form.payer" 
                            class="w-full mt-1"
                            placeholder="payer@example.com or +8801700000000" 
                            required 
                        />
                        <InputError :message="form.errors.payer" class="mt-1" />
                    </div>

                    <!-- Amount -->
                    <div>
                        <InputLabel for="amount" value="Amount (BDT)" />
                        <TextInput 
                            id="amount" 
                            type="number" 
                            min="10" 
                            v-model="form.amount" 
                            class="w-full mt-1"
                            placeholder="Enter requested amount (Min 10 BDT)" 
                            required 
                        />
                        <InputError :message="form.errors.amount" class="mt-1" />
                    </div>

                    <!-- Due Date (Required for Loans) -->
                    <div v-if="form.type === 'loan'">
                        <InputLabel for="due_at" value="Repayment Due Date" />
                        <TextInput 
                            id="due_at" 
                            type="date" 
                            v-model="form.due_at" 
                            class="w-full mt-1"
                            required
                        />
                        <InputError :message="form.errors.due_at" class="mt-1" />
                    </div>

                    <!-- Expires in Days -->
                    <div>
                        <InputLabel for="expires_in_days" value="Request Validity (Days)" />
                        <TextInput 
                            id="expires_in_days" 
                            type="number" 
                            min="1" 
                            max="30"
                            v-model="form.expires_in_days" 
                            class="w-full mt-1"
                        />
                        <InputError :message="form.errors.expires_in_days" class="mt-1" />
                    </div>

                    <!-- Note -->
                    <div>
                        <InputLabel for="note" value="Note / Purpose (Optional)" />
                        <TextInput 
                            id="note" 
                            type="text" 
                            v-model="form.note" 
                            class="w-full mt-1"
                            placeholder="e.g. Dinner bill split, emergency loan" 
                        />
                        <InputError :message="form.errors.note" class="mt-1" />
                    </div>

                    <!-- Pre-hold Checkbox -->
                    <div class="flex items-center gap-2 pt-2">
                        <input 
                            id="pre_hold" 
                            type="checkbox" 
                            v-model="form.pre_hold" 
                            class="checkbox checkbox-primary checkbox-sm" 
                        />
                        <InputLabel for="pre_hold" value="Pre-hold payer funds upon creation" class="cursor-pointer text-xs" />
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showCreateModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    color="primary" 
                    :disabled="form.processing" 
                    type="submit" 
                    form="create-request-form"
                >
                    Send Request
                </Button>
            </template>
        </DialogModal>

    </div>
</template>
