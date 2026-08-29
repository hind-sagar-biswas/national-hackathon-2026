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
import { show as transactionsShow } from '@/routes/transactions';
import { index, resendOtp, store } from '@/routes/transfers';
import { router, usePage } from '@inertiajs/vue3';
import { 
    AlertTriangle, 
    Ban,
    Clock,
    Eye, 
    Key, 
    Receipt, 
    RefreshCw, 
    Send, 
    ShieldAlert, 
    Wallet 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    account: Object,
    list: Object,
    filters: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Transfers' }],
});

const { user, can } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});

// User Account Status (Ban check)
const isUserRestricted = computed(() => {
    return user.value && user.value.is_active === false;
});

// Modal & OTP Challenge states
const showTransferModal = ref(false);
const isOtpChallengeActive = ref(false);

// OTP Cooldown Timer (60s)
const cooldownSeconds = ref(0);
let timerInterval = null;

const startCooldown = (seconds = 60) => {
    cooldownSeconds.value = seconds;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (cooldownSeconds.value > 0) {
            cooldownSeconds.value--;
        } else {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }, 1000);
};

const parseWaitSeconds = (msg) => {
    if (!msg || typeof msg !== 'string') return null;
    const match = msg.match(/Please wait (\d+) seconds/i);
    return match ? parseInt(match[1], 10) : null;
};

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const { form, submit, resetKey } = useIdempotentForm({
    recipient: '',
    amount: '',
    note: '',
    otp_code: '',
});

// Flash & page errors
const flash = computed(() => page.props.flash || {});
const pageErrors = computed(() => page.props.errors || {});

// Watch flash messages and errors for OTP challenge or cooldown messages
watch(
    () => [
        page.props.flash?.challenge_required, 
        form.errors.otp_code, 
        page.props.errors?.otp_code, 
        page.props.flash?.info, 
        page.props.flash?.warning, 
        page.props.flash?.error
    ],
    ([challenge, formErr, pageErr, flashInfo, flashWarn, flashErr]) => {
        if (challenge || formErr || pageErr || (typeof flashInfo === 'string' && flashInfo.includes('OTP'))) {
            isOtpChallengeActive.value = true;
            showTransferModal.value = true;

            if (cooldownSeconds.value === 0 && challenge) {
                startCooldown(60);
            }
        }

        const messages = [formErr, pageErr, flashInfo, flashWarn, flashErr];
        for (const msg of messages) {
            const waitSec = parseWaitSeconds(msg);
            if (waitSec) {
                startCooldown(waitSec);
                isOtpChallengeActive.value = true;
                showTransferModal.value = true;
                break;
            }
        }
    },
    { immediate: true, deep: true }
);

const handleTransferSubmit = () => {
    submit('post', store(), {
        preserveScroll: true,
        onSuccess: (pageState) => {
            const isChallenge = pageState?.props?.flash?.challenge_required || page.props.flash?.challenge_required;
            if (isChallenge) {
                isOtpChallengeActive.value = true;
                showTransferModal.value = true;
                if (cooldownSeconds.value === 0) {
                    startCooldown(60);
                }
            } else {
                isOtpChallengeActive.value = false;
                showTransferModal.value = false;
                form.reset();
            }
        },
        onError: (errors) => {
            isOtpChallengeActive.value = true;
            showTransferModal.value = true;
            if (errors?.otp_code) {
                const waitSec = parseWaitSeconds(errors.otp_code);
                if (waitSec) {
                    startCooldown(waitSec);
                }
            }
        },
    });
};

const handleResendOtp = () => {
    if (cooldownSeconds.value > 0) return;

    router.post(resendOtp(), {}, {
        preserveScroll: true,
        onSuccess: () => {
            startCooldown(60);
            isOtpChallengeActive.value = true;
            showTransferModal.value = true;
        },
        onError: (errors) => {
            isOtpChallengeActive.value = true;
            showTransferModal.value = true;
            const waitSec = parseWaitSeconds(errors?.otp_code);
            if (waitSec) {
                startCooldown(waitSec);
            }
        },
    });
};

const openTransferModal = () => {
    resetKey();
    form.reset();
    isOtpChallengeActive.value = false;
    showTransferModal.value = true;
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Account Banned / Restricted Alert Banner -->
        <div v-if="isUserRestricted" class="alert alert-error shadow-sm">
            <Ban class="size-6 text-error-content flex-shrink-0" />
            <div>
                <h3 class="font-bold text-sm text-error-content">Account Restricted</h3>
                <div class="text-xs text-error-content/90">
                    Your account has been restricted or banned by administration. You are currently not permitted to initiate transfers.
                </div>
            </div>
        </div>

        <!-- Risk Challenge Security Banner -->
        <div v-if="flash?.challenge_required" class="alert alert-warning shadow-sm border border-warning/40">
            <Key class="size-6 text-warning-content flex-shrink-0" />
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-sm text-warning-content">Security Verification Required</h3>
                    <span v-if="flash?.risk_score" class="badge badge-warning badge-sm font-bold">
                        Risk Score: {{ flash.risk_score }}
                    </span>
                </div>
                <div class="text-xs text-warning-content/90">
                    {{ flash?.info || 'A 6-digit OTP verification code has been dispatched to your contact to authorize this transfer.' }}
                </div>
            </div>
            <Button color="warning" size="sm" @click="showTransferModal = true">
                <Key class="size-4 me-1" /> Open Form to Enter OTP
            </Button>
        </div>

        <!-- Risk Hold Security Alert Banner -->
        <div v-if="flash?.transaction_held" class="alert alert-warning shadow-sm border border-warning/30">
            <ShieldAlert class="size-6 text-warning-content flex-shrink-0" />
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-sm text-warning-content">Transfer Held for Security Review</h3>
                    <span v-if="flash?.risk_score" class="badge badge-sm badge-outline font-bold">
                        Risk Score: {{ flash.risk_score }}
                    </span>
                </div>
                <div class="text-xs text-warning-content/90">
                    {{ flash?.warning || 'Your transfer triggered automated risk evaluation thresholds and has been temporarily reserved in an active hold pending compliance verification.' }}
                </div>
            </div>
        </div>

        <!-- Top Wallet Summary & Action Header -->
        <div class="card bg-base-200 shadow-sm border border-base-300">
            <div class="card-body p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center">
                            <Send size="24" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-base-content">
                                Money Transfer & Remittance
                            </h1>
                            <p class="text-xs text-base-content/60">
                                Send money instantly to any wallet using email address or phone number.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <span class="text-xs text-base-content/60 block">Available Balance</span>
                            <span class="text-lg font-bold text-primary">
                                {{ account?.available_balance?.formatted ?? '0.00' }} {{ account?.currency ?? 'BDT' }}
                            </span>
                        </div>

                        <Button 
                            color="primary" 
                            :disabled="isUserRestricted || !can('create-transfers')" 
                            @click="openTransferModal"
                        >
                            <Send class="inline-block me-1" size="16" /> Send Money
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main List Layout for History Table -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-3"
                    placeholder="Search transfers by reference or ID..." />
                
                <SelectBox v-model="filters.type" placeholder="All Types" class="col-span-1 md:col-span-2"
                    :options="[
                        { label: 'Transfer', value: 'transfer' },
                        { label: 'Deposit', value: 'deposit' },
                        { label: 'Money Request', value: 'money_request' },
                        { label: 'Loan', value: 'loan' },
                    ]" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Transactions DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 50rem" class="bg-base-100">
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
                    <Column header="Initiated By">
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
                    <Column header="Receipt">
                        <template #body="slotProps">
                            <Button as="link" :href="transactionsShow({ transaction: slotProps.data.id })" color="secondary" size="sm">
                                <Eye class="inline-block" size="16" />
                            </Button>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

        <!-- Send Money Modal -->
        <DialogModal :show="showTransferModal" @close="showTransferModal = false">
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2 text-primary">
                        <Send class="size-5" />
                        <span>Send Money</span>
                    </div>

                    <span v-if="flash?.risk_score" class="badge badge-warning badge-sm font-bold">
                        Risk Score: {{ flash.risk_score }}
                    </span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleTransferSubmit" id="transfer-form" class="space-y-4">
                    <!-- Recipient -->
                    <div>
                        <InputLabel for="recipient" value="Recipient (Email or Phone)" />
                        <TextInput id="recipient" type="text" v-model="form.recipient" class="w-full mt-1"
                            placeholder="user@example.com or +8801700000000" required />
                        <InputError :message="form.errors.recipient" class="mt-1" />
                    </div>

                    <!-- Amount -->
                    <div>
                        <InputLabel for="amount" value="Amount (BDT)" />
                        <TextInput id="amount" type="number" min="1" v-model="form.amount" class="w-full mt-1"
                            placeholder="Enter amount" required />
                        <InputError :message="form.errors.amount" class="mt-1" />
                    </div>

                    <!-- Note -->
                    <div>
                        <InputLabel for="note" value="Note / Reference (Optional)" />
                        <TextInput id="note" type="text" v-model="form.note" class="w-full mt-1"
                            placeholder="e.g. Dinner share, rent payment" />
                        <InputError :message="form.errors.note" class="mt-1" />
                    </div>

                    <!-- OTP Code Field (Added directly to form ONLY when OTP is required) -->
                    <div v-if="isOtpChallengeActive || cooldownSeconds > 0" class="pt-3 border-t border-base-200 bg-warning/10 p-3 rounded-lg border border-warning/40 transition-all">
                        <div class="flex justify-between items-center mb-1">
                            <InputLabel for="otp_code" value="Security Verification (OTP Code)" class="font-bold text-warning-content" />
                            <span class="badge badge-warning badge-xs font-bold">
                                OTP Required
                            </span>
                        </div>
                        
                        <p class="text-xs text-warning-content font-medium mb-2">
                            {{ flash?.info || 'A 6-digit OTP code has been sent to your registered contact. Write it below to complete transfer.' }}
                        </p>

                        <TextInput 
                            id="otp_code" 
                            type="text" 
                            v-model="form.otp_code" 
                            class="w-full mt-1 font-mono tracking-widest text-center text-lg font-bold border-warning focus:border-warning focus:ring-warning bg-base-100"
                            placeholder="Enter 6-digit OTP code" 
                            maxlength="10" 
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.otp_code || pageErrors?.otp_code" class="mt-1 font-semibold" />

                        <!-- Resend OTP & Cooldown Counter -->
                        <div class="flex justify-between items-center text-xs mt-2 pt-2 border-t border-warning/20">
                            <span v-if="cooldownSeconds > 0" class="text-warning font-bold flex items-center gap-1">
                                <Clock class="size-3" /> Please wait {{ cooldownSeconds }} seconds before requesting a new OTP.
                            </span>
                            <span v-else class="text-base-content/60">
                                Didn't receive code?
                            </span>

                            <Button 
                                color="ghost" 
                                size="xs" 
                                :disabled="cooldownSeconds > 0 || form.processing" 
                                @click="handleResendOtp" 
                                class="gap-1 text-primary font-bold"
                                type="button"
                            >
                                <RefreshCw class="size-3" :class="{ 'animate-spin': form.processing }" />
                                {{ cooldownSeconds > 0 ? `Wait (${cooldownSeconds}s)` : 'Resend OTP' }}
                            </Button>
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showTransferModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    :color="isOtpChallengeActive ? 'warning' : 'primary'" 
                    :disabled="form.processing" 
                    type="submit" 
                    form="transfer-form"
                >
                    {{ isOtpChallengeActive ? 'Verify OTP & Complete Transfer' : 'Confirm & Send Transfer' }}
                </Button>
            </template>
        </DialogModal>

    </div>
</template>
