<script setup>
import Button from '@/Components/Buttons/Button.vue';
import { useAuth } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { 
    accept as acceptSplit, 
    destroy as cancelSplit, 
    index as billSplitsIndex, 
    reject as rejectSplit 
} from '@/routes/bill-splits';
import { router } from '@inertiajs/vue3';
import { 
    AlertCircle, 
    AlertTriangle, 
    ArrowLeft, 
    Calendar, 
    Check, 
    CheckCircle2, 
    Clock, 
    DollarSign, 
    Eye, 
    HandCoins, 
    Lock, 
    Receipt, 
    Scale, 
    ShieldAlert, 
    ShieldCheck, 
    Trash2, 
    Unlock, 
    User, 
    UserCheck, 
    Users, 
    X, 
    XCircle 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed } from 'vue';

const props = defineProps({
    billSplit: Object,
    currentUserParticipant: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: `Bill Split: ${props.billSplit?.title || 'Details'}` }],
});

const { user } = useAuth();

const isInitiator = computed(() => {
    return props.billSplit?.initiator?.id === user.value?.id;
});

const acceptedCount = computed(() => {
    return props.billSplit?.participants?.filter(p => p.status === 'accepted' || p.status === 'settled').length || 0;
});

const totalParticipants = computed(() => {
    return props.billSplit?.participants?.length || 1;
});

const acceptedAmountSum = computed(() => {
    if (!props.billSplit?.participants) return 0;
    return props.billSplit.participants
        .filter(p => p.status === 'accepted' || p.status === 'settled')
        .reduce((sum, p) => sum + (p.share_amount?.raw || p.share_amount || 0), 0);
});

const progressPercentage = computed(() => {
    const total = props.billSplit?.total_amount?.raw || props.billSplit?.total_amount || 1;
    return Math.min(100, Math.round((acceptedAmountSum.value / total) * 100));
});

const handleAccept = () => {
    if (confirm(`Accept your share of ${props.currentUserParticipant?.share_amount?.formatted} BDT? This amount will be temporarily held in escrow until all participants confirm.`)) {
        const idempotencyKey = typeof crypto !== 'undefined' && crypto.randomUUID 
            ? crypto.randomUUID() 
            : 'bs_accept_' + Date.now() + '_' + Math.random().toString(36).substring(2);
        
        router.post(acceptSplit({ billSplit: props.billSplit.id }), {}, {
            headers: { 'X-Idempotency-Key': idempotencyKey },
            preserveScroll: true,
        });
    }
};

const handleReject = () => {
    if (confirm('Are you sure you want to decline this bill split invitation?')) {
        router.post(rejectSplit({ billSplit: props.billSplit.id }), {}, {
            preserveScroll: true,
        });
    }
};

const handleCancel = () => {
    if (confirm('Are you sure you want to cancel this entire bill split? Any existing participant holds will be immediately released.')) {
        router.delete(cancelSplit({ billSplit: props.billSplit.id }), {
            onSuccess: () => router.visit(billSplitsIndex()),
        });
    }
};
</script>

<template>
    <div class="p-5 space-y-6 max-w-5xl mx-auto">

        <!-- Top Navigation Bar -->
        <div class="flex items-center justify-between">
            <Button as="link" :href="billSplitsIndex()" color="neutral" ghost size="sm">
                <ArrowLeft class="size-4 me-1" /> Back to Bill Splits
            </Button>

            <div class="flex items-center gap-2">
                <Button 
                    v-if="isInitiator && billSplit.status === 'pending'"
                    color="error" 
                    soft 
                    size="sm" 
                    @click="handleCancel"
                >
                    <Trash2 class="size-4 me-1" /> Cancel Bill Split
                </Button>
            </div>
        </div>

        <!-- Current User Participation Banner (Action Required) -->
        <div 
            v-if="currentUserParticipant && currentUserParticipant.status === 'pending' && billSplit.status === 'pending'" 
            class="card bg-warning/15 border-2 border-warning/40 shadow-sm"
        >
            <div class="card-body p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning text-warning-content rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <HandCoins size="20" />
                    </div>
                    <div>
                        <h3 class="font-bold text-base-content text-sm">
                            Your Action Required
                        </h3>
                        <p class="text-xs text-base-content/70">
                            You are invited to pay your share of <strong class="text-base-content">{{ currentUserParticipant.share_amount?.formatted }} BDT</strong>. 
                            Accepting will place a temporary hold on your balance until all members accept.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto">
                    <Button color="success" size="sm" class="flex-1 sm:flex-none" @click="handleAccept">
                        <Check class="size-4 me-1" /> Accept & Reserve Share
                    </Button>
                    <Button color="error" ghost size="sm" class="flex-1 sm:flex-none" @click="handleReject">
                        <X class="size-4 me-1" /> Decline
                    </Button>
                </div>
            </div>
        </div>

        <!-- Current User Accepted Banner -->
        <div 
            v-else-if="currentUserParticipant && (currentUserParticipant.status === 'accepted' || currentUserParticipant.status === 'settled')" 
            class="alert alert-success/15 border border-success/30 text-xs py-3"
        >
            <ShieldCheck class="size-5 text-success" />
            <div>
                <span class="font-bold">Share Confirmed:</span> Your share of <strong>{{ currentUserParticipant.share_amount?.formatted }} BDT</strong> is 
                <span v-if="billSplit.status === 'completed'">settled successfully.</span>
                <span v-else>held securely in escrow waiting for other participants.</span>
            </div>
        </div>

        <!-- Main Bill Split Summary Card -->
        <div class="card bg-base-100 shadow-md border border-base-300">
            <div class="card-body p-6 space-y-6">

                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-base-200">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl font-bold text-base-content tracking-tight">
                                {{ billSplit.title }}
                            </h1>
                            <span class="badge badge-lg uppercase font-mono font-bold" :class="{
                                'badge-primary': billSplit.mode === 'equal',
                                'badge-secondary': billSplit.mode === 'exact',
                                'badge-accent': billSplit.mode === 'percentage',
                                'badge-info': billSplit.mode === 'shares',
                            }">
                                {{ billSplit.mode_label || billSplit.mode }}
                            </span>
                        </div>

                        <div class="text-xs text-base-content/60 flex flex-wrap items-center gap-3 mt-1.5">
                            <span v-if="billSplit.merchant_name" class="font-semibold text-base-content/80">
                                🏪 {{ billSplit.merchant_name }}
                            </span>
                            <span>Created {{ billSplit.created_at?.formatted }}</span>
                            <span v-if="billSplit.expires_at?.formatted && billSplit.status === 'pending'" class="text-warning flex items-center gap-1">
                                <Clock class="size-3" /> Expires {{ billSplit.expires_at.formatted }}
                            </span>
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <div class="text-xs text-base-content/50 uppercase font-semibold">Total Split Amount</div>
                        <div class="text-3xl font-extrabold text-primary">
                            {{ billSplit.total_amount?.formatted }} <span class="text-sm font-semibold text-base-content/70">BDT</span>
                        </div>
                        <span class="badge uppercase font-bold text-xs mt-1" :class="{
                            'badge-warning': billSplit.status === 'pending',
                            'badge-success text-white': billSplit.status === 'completed',
                            'badge-neutral': billSplit.status === 'cancelled',
                            'badge-error': billSplit.status === 'failed',
                        }">
                            {{ billSplit.status_label || billSplit.status }}
                        </span>
                    </div>
                </div>

                <!-- Progress & Settlement Meter -->
                <div class="bg-base-200/60 p-4 rounded-box space-y-2 border border-base-300">
                    <div class="flex justify-between items-center text-xs font-bold">
                        <span class="text-base-content flex items-center gap-1.5">
                            <Users class="size-4 text-primary" />
                            <span>Acceptance Progress: {{ acceptedCount }} of {{ totalParticipants }} Participants</span>
                        </span>
                        <span class="font-mono text-primary">
                            {{ progressPercentage }}% ({{ (acceptedAmountSum / 100).toFixed(2) }} / {{ billSplit.total_amount?.formatted }} BDT)
                        </span>
                    </div>

                    <progress 
                        class="progress progress-primary w-full h-3" 
                        :value="acceptedAmountSum" 
                        :max="billSplit.total_amount?.raw || billSplit.total_amount || 1"
                    ></progress>

                    <div class="flex justify-between items-center text-[11px] text-base-content/60 pt-0.5">
                        <span>Initiated by: <strong>{{ billSplit.initiator?.name }}</strong> ({{ billSplit.initiator?.email }})</span>
                        <span v-if="billSplit.settled_at?.formatted">Settled {{ billSplit.settled_at.formatted }}</span>
                    </div>
                </div>

                <!-- Note / Description if present -->
                <div v-if="billSplit.note" class="bg-base-200/40 p-3.5 rounded-lg border border-base-300 text-xs">
                    <span class="font-bold text-base-content/70 block mb-1">Split Note & Context:</span>
                    <p class="text-base-content/80">{{ billSplit.note }}</p>
                </div>

                <!-- Participants Table Section -->
                <div class="space-y-3 pt-2">
                    <h2 class="text-base font-bold text-base-content flex items-center gap-2">
                        <UserCheck class="size-5 text-primary" />
                        <span>Participants Breakdown</span>
                    </h2>

                    <div class="overflow-x-auto shadow-sm rounded-lg border border-base-300">
                        <DataTable :value="billSplit.participants || []" class="bg-base-100 text-xs">
                            
                            <!-- Participant Info -->
                            <Column header="Participant">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-2.5">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                                <img v-if="data.user?.profile_photo_url" :src="data.user.profile_photo_url" :alt="data.user.name" />
                                                <span v-else class="text-xs">{{ data.user?.name?.charAt(0) || 'U' }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-base-content flex items-center gap-1.5">
                                                <span>{{ data.user?.name }}</span>
                                                <span v-if="data.is_initiator" class="badge badge-xs badge-primary font-bold">Initiator</span>
                                                <span v-if="data.user?.id === user?.id" class="badge badge-xs badge-neutral">You</span>
                                            </div>
                                            <div class="text-base-content/50 text-[11px]">{{ data.user?.email || data.user?.phone }}</div>
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <!-- Mode Share Value -->
                            <Column header="Allocated Ratio / Share">
                                <template #body="{ data }">
                                    <span v-if="billSplit.mode === 'percentage'" class="font-mono font-medium">
                                        {{ data.share_value ? `${data.share_value}%` : 'Calculated' }}
                                    </span>
                                    <span v-else-if="billSplit.mode === 'shares'" class="font-mono font-medium">
                                        {{ data.share_value || 1 }} Share(s)
                                    </span>
                                    <span v-else-if="billSplit.mode === 'exact'" class="font-mono font-medium">
                                        {{ data.share_amount?.formatted }} BDT
                                    </span>
                                    <span v-else class="font-mono font-medium text-base-content/60">
                                        Equal Share
                                    </span>
                                </template>
                            </Column>

                            <!-- Individual Share Amount -->
                            <Column header="Amount to Pay">
                                <template #body="{ data }">
                                    <div class="font-mono font-bold text-sm text-base-content">
                                        {{ data.share_amount?.formatted }} <span class="text-xs font-normal text-base-content/60">BDT</span>
                                    </div>
                                </template>
                            </Column>

                            <!-- Hold & Escrow Status -->
                            <Column header="Escrow Hold">
                                <template #body="{ data }">
                                    <div v-if="data.hold_id" class="flex items-center gap-1 text-warning font-semibold">
                                        <Lock class="size-3.5" />
                                        <span>Hold #{{ data.hold_id }}</span>
                                    </div>
                                    <div v-else-if="data.is_initiator" class="text-base-content/50">
                                        Initiator
                                    </div>
                                    <div v-else class="text-base-content/40">
                                        None
                                    </div>
                                </template>
                            </Column>

                            <!-- Status -->
                            <Column header="Participant Status">
                                <template #body="{ data }">
                                    <div class="space-y-0.5">
                                        <span class="badge font-bold uppercase text-[11px]" :class="{
                                            'badge-warning': data.status === 'pending',
                                            'badge-success text-white': data.status === 'accepted' || data.status === 'settled',
                                            'badge-error': data.status === 'rejected',
                                        }">
                                            {{ data.status_label || data.status }}
                                        </span>
                                        <div v-if="data.accepted_at?.formatted" class="text-[10px] text-base-content/50">
                                            {{ data.accepted_at.formatted }}
                                        </div>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
