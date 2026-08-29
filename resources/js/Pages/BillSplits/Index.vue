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
    accept as acceptSplit, 
    destroy as cancelSplit, 
    index, 
    reject as rejectSplit, 
    show as billSplitsShow, 
    store 
} from '@/routes/bill-splits';
import { router, usePage } from '@inertiajs/vue3';
import { 
    AlertCircle, 
    AlertTriangle, 
    Check, 
    Clock, 
    DollarSign, 
    Eye, 
    HandCoins, 
    MinusCircle, 
    Percent, 
    PieChart, 
    PlusCircle, 
    Receipt, 
    Scale, 
    ShieldAlert, 
    Trash2, 
    User, 
    UserCheck, 
    Users, 
    X 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    list: Object,
    filters: Object,
    modeOptions: Array,
    statusOptions: Array,
    counts: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Bill Splits' }],
});

const { user } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), {
    tab: props.filters?.tab || 'participating',
    status: props.filters?.status || '',
    mode: props.filters?.mode || '',
    search: props.filters?.search || '',
}, {
    debounceMs: { search: 500 },
});

// Watch tab prop
watch(
    () => props.filters?.tab,
    (newTab) => {
        if (newTab && filters.tab !== newTab) {
            filters.tab = newTab;
        }
    }
);

// Create modal state
const showCreateModal = ref(false);

const { form, submit, resetKey } = useIdempotentForm({
    title: '',
    total_amount: '',
    mode: 'equal',
    merchant_name: '',
    note: '',
    expires_in_days: 3,
    participants: [
        { identifier: '', value: '' },
    ],
});

const addParticipant = () => {
    form.participants.push({ identifier: '', value: '' });
};

const removeParticipant = (index) => {
    if (form.participants.length > 1) {
        form.participants.splice(index, 1);
    }
};

const openCreateModal = () => {
    resetKey();
    form.reset();
    form.mode = 'equal';
    form.expires_in_days = 3;
    form.participants = [{ identifier: '', value: '' }];
    showCreateModal.value = true;
};

// Calculations preview for modal
const computedParticipantsBreakdown = computed(() => {
    const total = parseFloat(form.total_amount) || 0;
    if (total <= 0) return [];

    const activeParticipants = form.participants.filter(p => p.identifier && p.identifier.trim() !== '');
    const count = activeParticipants.length + 1; // +1 for current user (initiator)

    if (form.mode === 'equal') {
        const share = total / count;
        return [
            { name: 'You (Initiator)', amount: share.toFixed(2), shareText: `${(100 / count).toFixed(1)}%` },
            ...activeParticipants.map((p) => ({
                name: p.identifier,
                amount: share.toFixed(2),
                shareText: `${(100 / count).toFixed(1)}%`,
            })),
        ];
    } else if (form.mode === 'exact') {
        const participantSum = activeParticipants.reduce((acc, p) => acc + (parseFloat(p.value) || 0), 0);
        const initiatorShare = Math.max(0, total - participantSum);
        return [
            { name: 'You (Initiator)', amount: initiatorShare.toFixed(2), shareText: 'Remainder' },
            ...activeParticipants.map((p) => ({
                name: p.identifier,
                amount: (parseFloat(p.value) || 0).toFixed(2),
                shareText: `${(parseFloat(p.value) || 0).toFixed(2)} BDT`,
            })),
        ];
    } else if (form.mode === 'percentage') {
        const participantPctSum = activeParticipants.reduce((acc, p) => acc + (parseFloat(p.value) || 0), 0);
        const initiatorPct = Math.max(0, 100 - participantPctSum);
        return [
            { name: 'You (Initiator)', amount: ((total * initiatorPct) / 100).toFixed(2), shareText: `${initiatorPct.toFixed(1)}%` },
            ...activeParticipants.map((p) => {
                const pct = parseFloat(p.value) || 0;
                return {
                    name: p.identifier,
                    amount: ((total * pct) / 100).toFixed(2),
                    shareText: `${pct.toFixed(1)}%`,
                };
            }),
        ];
    } else if (form.mode === 'shares') {
        const participantShares = activeParticipants.reduce((acc, p) => acc + (parseFloat(p.value) || 1), 0);
        const totalShares = 1 + participantShares; // 1 share for initiator
        const perShare = total / totalShares;
        return [
            { name: 'You (Initiator)', amount: perShare.toFixed(2), shareText: '1 Share' },
            ...activeParticipants.map((p) => {
                const shares = parseFloat(p.value) || 1;
                return {
                    name: p.identifier,
                    amount: (perShare * shares).toFixed(2),
                    shareText: `${shares} Share(s)`,
                };
            }),
        ];
    }
    return [];
});

const handleCreateSubmit = () => {
    submit('post', store(), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const handleAccept = (item) => {
    if (confirm(`Accept your share of "${item.title}"? Your share will be reserved on hold until all participants accept.`)) {
        const idempotencyKey = typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : 'bs_accept_' + Date.now() + '_' + Math.random().toString(36).substring(2);
        router.post(acceptSplit({ billSplit: item.id }), {}, {
            headers: { 'X-Idempotency-Key': idempotencyKey },
            preserveScroll: true,
        });
    }
};

const handleReject = (item) => {
    if (confirm(`Decline your participation in "${item.title}"?`)) {
        router.post(rejectSplit({ billSplit: item.id }), {}, { preserveScroll: true });
    }
};

const handleCancel = (item) => {
    if (confirm(`Cancel bill split "${item.title}"? Any reserved participant holds will be immediately released.`)) {
        router.delete(cancelSplit({ billSplit: item.id }), { preserveScroll: true });
    }
};

const getUserParticipant = (item) => {
    if (!item.participants || !user.value) return null;
    return item.participants.find(p => p.user?.id === user.value.id);
};

const getAcceptedCount = (item) => {
    if (!item.participants) return 0;
    return item.participants.filter(p => p.status === 'accepted').length;
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Header Card -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <Users size="24" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-content">
                            Shared Expense & Bill Splitting
                        </h1>
                        <p class="text-xs text-base-content/60">
                            Equally or proportionally divide group expenses, merchant dinners, and shared invoices with automated hold escrow.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button color="primary" size="sm" @click="openCreateModal">
                        <PlusCircle class="inline-block me-1" size="16" /> Create Bill Split
                    </Button>
                </div>
            </div>
        </div>

        <!-- Pending Action Notification Banner -->
        <div v-if="counts?.pending_action > 0" class="alert alert-warning shadow-sm border border-warning/20">
            <ShieldAlert class="size-5" />
            <div class="text-sm">
                <span class="font-bold">Action Required:</span> You have <strong>{{ counts.pending_action }}</strong> pending bill split invitation(s) awaiting your acceptance.
            </div>
            <button class="btn btn-xs btn-outline" @click="filters.tab = 'participating'; filters.status = 'pending'">
                View Pending
            </button>
        </div>

        <!-- Tab Controls & Filter Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="tabs tabs-boxed bg-base-200 p-1">
                <button 
                    class="tab font-semibold transition-all duration-200" 
                    :class="{ 'tab-active': filters.tab === 'participating' }"
                    @click="filters.tab = 'participating'"
                >
                    <Users class="size-4 me-1.5 inline" /> Participating In
                    <span v-if="counts?.pending_action > 0" class="badge badge-xs badge-warning ms-1.5 font-bold">
                        {{ counts.pending_action }}
                    </span>
                </button>
                <button 
                    class="tab font-semibold transition-all duration-200" 
                    :class="{ 'tab-active': filters.tab === 'created' }"
                    @click="filters.tab = 'created'"
                >
                    <UserCheck class="size-4 me-1.5 inline" /> Created by Me
                </button>
            </div>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-3"
                    placeholder="Search by Title, Merchant, or Note..." />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="statusOptions" />

                <SelectBox v-model="filters.mode" placeholder="All Split Modes" class="col-span-1 md:col-span-2"
                    :options="modeOptions" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list?.data || []" tableStyle="min-width: 60rem" class="bg-base-100">
                    
                    <!-- Split Details & Merchant -->
                    <Column header="Split Title & Merchant">
                        <template #body="{ data }">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-base-content hover:text-primary transition-colors cursor-pointer"
                                          @click="router.visit(billSplitsShow({ billSplit: data.id }))">
                                        {{ data.title }}
                                    </span>
                                    <span class="badge badge-xs font-mono uppercase" :class="{
                                        'badge-primary': data.mode === 'equal',
                                        'badge-secondary': data.mode === 'exact',
                                        'badge-accent': data.mode === 'percentage',
                                        'badge-info': data.mode === 'shares',
                                    }">
                                        {{ data.mode_label || data.mode }}
                                    </span>
                                </div>
                                <div class="text-xs text-base-content/60 flex items-center gap-2">
                                    <span v-if="data.merchant_name" class="font-medium text-base-content/80">
                                        🏪 {{ data.merchant_name }}
                                    </span>
                                    <span v-if="data.merchant_name && data.created_at?.formatted">•</span>
                                    <span>Created {{ data.created_at?.formatted }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Initiator -->
                    <Column header="Initiator">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                        <img v-if="data.initiator?.profile_photo_url" :src="data.initiator.profile_photo_url" :alt="data.initiator.name" />
                                        <span v-else class="text-xs">{{ data.initiator?.name?.charAt(0) || 'U' }}</span>
                                    </div>
                                </div>
                                <div class="text-xs">
                                    <div class="font-semibold text-base-content">
                                        {{ data.initiator?.id === user?.id ? 'You' : data.initiator?.name }}
                                    </div>
                                    <div class="text-base-content/50">{{ data.initiator?.email }}</div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Total Amount & User Share -->
                    <Column header="Total & Your Share">
                        <template #body="{ data }">
                            <div class="space-y-0.5">
                                <div class="font-bold text-sm text-base-content">
                                    {{ data.total_amount?.formatted ?? data.total_amount }} BDT
                                </div>
                                <div v-if="getUserParticipant(data)" class="text-xs text-primary font-medium">
                                    Your Share: {{ getUserParticipant(data)?.share_amount?.formatted }} BDT
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Acceptance Progress -->
                    <Column header="Participants Progress">
                        <template #body="{ data }">
                            <div class="space-y-1.5 w-36">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-base-content/70">
                                        {{ getAcceptedCount(data) }} / {{ data.participants_count || data.participants?.length }} Accepted
                                    </span>
                                </div>
                                <progress 
                                    class="progress progress-primary w-full h-2" 
                                    :value="getAcceptedCount(data)" 
                                    :max="data.participants_count || data.participants?.length || 1"
                                ></progress>
                            </div>
                        </template>
                    </Column>

                    <!-- Status -->
                    <Column header="Status">
                        <template #body="{ data }">
                            <span class="badge font-bold uppercase text-xs" :class="{
                                'badge-warning': data.status === 'pending',
                                'badge-success text-white': data.status === 'completed',
                                'badge-neutral': data.status === 'cancelled',
                                'badge-error': data.status === 'failed',
                            }">
                                {{ data.status_label || data.status }}
                            </span>
                            <div v-if="data.expires_at?.formatted && data.status === 'pending'" class="text-[11px] text-base-content/50 mt-1 flex items-center gap-1">
                                <Clock class="size-3" /> Expires {{ data.expires_at.formatted }}
                            </div>
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column header="Actions">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button 
                                    as="link" 
                                    :href="billSplitsShow({ billSplit: data.id })" 
                                    color="neutral" 
                                    ghost 
                                    size="xs"
                                    title="View Bill Details"
                                >
                                    <Eye class="size-4" />
                                </Button>

                                <!-- Participant Actions -->
                                <template v-if="getUserParticipant(data)?.status === 'pending' && data.status === 'pending'">
                                    <Button 
                                        color="success" 
                                        size="xs" 
                                        @click="handleAccept(data)"
                                        title="Accept & Place Hold"
                                    >
                                        <Check class="size-3.5 me-1" /> Accept
                                    </Button>

                                    <Button 
                                        color="error" 
                                        ghost 
                                        size="xs" 
                                        @click="handleReject(data)"
                                        title="Decline Split"
                                    >
                                        <X class="size-3.5" />
                                    </Button>
                                </template>

                                <!-- Initiator Cancel Action -->
                                <Button 
                                    v-if="data.initiator?.id === user?.id && data.status === 'pending'"
                                    color="error" 
                                    ghost 
                                    size="xs" 
                                    @click="handleCancel(data)"
                                    title="Cancel Bill Split"
                                >
                                    <Trash2 class="size-3.5 text-error" />
                                </Button>
                            </div>
                        </template>
                    </Column>

                    <template #empty>
                        <div class="text-center py-12 text-base-content/60">
                            <Receipt class="size-12 mx-auto mb-2 opacity-30" />
                            <p class="font-medium text-base">No bill splits found</p>
                            <p class="text-xs text-base-content/40 mt-1">
                                Create a bill split to divide expenses with your team or friends.
                            </p>
                        </div>
                    </template>
                </DataTable>
            </div>
        </ListLayout>

        <!-- Create Bill Split Modal -->
        <DialogModal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <template #title>
                <div class="flex items-center gap-2 text-primary">
                    <Receipt class="size-5" />
                    <span>Create Multi-Participant Bill Split</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleCreateSubmit" id="create-bill-split-form" class="space-y-4">
                    
                    <!-- Title -->
                    <div>
                        <InputLabel for="split-title" value="Expense / Bill Title" />
                        <TextInput 
                            id="split-title" 
                            type="text" 
                            v-model="form.title" 
                            class="w-full mt-1"
                            placeholder="e.g. Dinner at Gulshan Club, Team Cloud Hosting" 
                            required 
                        />
                        <InputError :message="form.errors.title" class="mt-1" />
                    </div>

                    <!-- Total Amount & Expiry Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="split-total-amount" value="Total Bill Amount (BDT)" />
                            <TextInput 
                                id="split-total-amount" 
                                type="number" 
                                min="10" 
                                step="0.01" 
                                v-model="form.total_amount" 
                                class="w-full mt-1"
                                placeholder="0.00" 
                                required 
                            />
                            <InputError :message="form.errors.total_amount" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="split-expires" value="Invitation Expiration (Days)" />
                            <TextInput 
                                id="split-expires" 
                                type="number" 
                                min="1" 
                                max="30" 
                                v-model="form.expires_in_days" 
                                class="w-full mt-1"
                            />
                            <InputError :message="form.errors.expires_in_days" class="mt-1" />
                        </div>
                    </div>

                    <!-- Split Mode Selector -->
                    <div>
                        <InputLabel value="Split Calculation Mode" class="mb-2" />
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <label 
                                class="border rounded-lg p-3 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                                :class="form.mode === 'equal' ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm' : 'border-base-300 hover:bg-base-200'"
                            >
                                <input type="radio" value="equal" v-model="form.mode" class="hidden" />
                                <Scale class="size-5" />
                                <span class="text-xs">Equal Split</span>
                            </label>

                            <label 
                                class="border rounded-lg p-3 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                                :class="form.mode === 'exact' ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm' : 'border-base-300 hover:bg-base-200'"
                            >
                                <input type="radio" value="exact" v-model="form.mode" class="hidden" />
                                <DollarSign class="size-5" />
                                <span class="text-xs">Exact Amounts</span>
                            </label>

                            <label 
                                class="border rounded-lg p-3 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                                :class="form.mode === 'percentage' ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm' : 'border-base-300 hover:bg-base-200'"
                            >
                                <input type="radio" value="percentage" v-model="form.mode" class="hidden" />
                                <Percent class="size-5" />
                                <span class="text-xs">Percentages</span>
                            </label>

                            <label 
                                class="border rounded-lg p-3 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                                :class="form.mode === 'shares' ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm' : 'border-base-300 hover:bg-base-200'"
                            >
                                <input type="radio" value="shares" v-model="form.mode" class="hidden" />
                                <PieChart class="size-5" />
                                <span class="text-xs">Ratios / Shares</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.mode" class="mt-1" />
                    </div>

                    <!-- Dynamic Participants List -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <InputLabel value="Other Participants (Email or Phone)" />
                            <Button type="button" color="neutral" ghost size="xs" @click="addParticipant">
                                <PlusCircle class="size-3.5 me-1" /> Add Person
                            </Button>
                        </div>

                        <div class="space-y-2 max-h-52 overflow-y-auto p-1">
                            <div 
                                v-for="(participant, idx) in form.participants" 
                                :key="idx" 
                                class="flex items-center gap-2 bg-base-200/50 p-2 rounded-lg border border-base-300"
                            >
                                <div class="flex-1">
                                    <TextInput 
                                        type="text" 
                                        v-model="participant.identifier" 
                                        placeholder="email@example.com or +8801700000000" 
                                        class="w-full text-xs"
                                        required 
                                    />
                                </div>

                                <!-- Value field for exact, percentage, or shares mode -->
                                <div v-if="form.mode !== 'equal'" class="w-32">
                                    <TextInput 
                                        type="number" 
                                        step="any" 
                                        v-model="participant.value" 
                                        :placeholder="form.mode === 'exact' ? 'Amount (BDT)' : form.mode === 'percentage' ? 'Percentage %' : 'Shares count'" 
                                        class="w-full text-xs"
                                        required 
                                    />
                                </div>

                                <button 
                                    type="button" 
                                    class="btn btn-ghost btn-xs text-error btn-circle"
                                    :disabled="form.participants.length <= 1"
                                    @click="removeParticipant(idx)"
                                >
                                    <MinusCircle class="size-4" />
                                </button>
                            </div>
                        </div>
                        <InputError :message="form.errors.participants" class="mt-1" />
                    </div>

                    <!-- Realtime Calculation Preview -->
                    <div v-if="computedParticipantsBreakdown.length > 0" class="bg-base-200 p-3.5 rounded-lg text-xs space-y-2 border border-base-300">
                        <div class="font-bold text-base-content flex items-center justify-between">
                            <span>Live Allocation Preview</span>
                            <span class="text-primary font-mono font-bold">{{ form.total_amount }} BDT Total</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            <div 
                                v-for="(item, idx) in computedParticipantsBreakdown" 
                                :key="idx"
                                class="flex justify-between items-center bg-base-100 px-2.5 py-1.5 rounded border border-base-300/60"
                            >
                                <span class="truncate max-w-[140px] font-medium text-base-content/80">{{ item.name }}</span>
                                <span class="font-mono font-bold text-base-content">
                                    {{ item.amount }} BDT <span class="text-[10px] text-base-content/50">({{ item.shareText }})</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Merchant Name & Note Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <InputLabel for="split-merchant" value="Merchant / Vendor Name (Optional)" />
                            <TextInput 
                                id="split-merchant" 
                                type="text" 
                                v-model="form.merchant_name" 
                                class="w-full mt-1"
                                placeholder="e.g. Starbucks, Uber, Netflix" 
                            />
                            <InputError :message="form.errors.merchant_name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="split-note" value="Note / Invoice Details (Optional)" />
                            <TextInput 
                                id="split-note" 
                                type="text" 
                                v-model="form.note" 
                                class="w-full mt-1"
                                placeholder="e.g. Friday team lunch" 
                            />
                            <InputError :message="form.errors.note" class="mt-1" />
                        </div>
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
                    form="create-bill-split-form"
                >
                    Create & Send Invitations
                </Button>
            </template>
        </DialogModal>
    </div>
</template>
