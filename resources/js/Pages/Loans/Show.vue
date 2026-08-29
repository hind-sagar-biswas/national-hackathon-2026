<script setup>
import Button from '@/Components/Buttons/Button.vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { useAuth, useIdempotentForm } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { index as loansIndex, repay, waive } from '@/routes/loans';
import { show as transactionsShow } from '@/routes/transactions';
import { router } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    Banknote, 
    Calendar, 
    CheckCircle2, 
    Clock, 
    Eye, 
    FileText, 
    HandCoins, 
    User 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { ref } from 'vue';

const props = defineProps({
    loan: Object,
    is_lender: Boolean,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: `Loan #${props.loan?.id}` }],
});

const { user } = useAuth();
const showRepayModal = ref(false);

const repayForm = useIdempotentForm({
    amount: '',
});

const openRepayModal = () => {
    repayForm.resetKey();
    repayForm.form.reset();
    const rawPaisa = props.loan?.outstanding_amount?.raw ?? props.loan?.outstanding_amount;
    repayForm.form.amount = typeof rawPaisa === 'number' ? (rawPaisa / 100) : rawPaisa;
    showRepayModal.value = true;
};

const handleRepaySubmit = () => {
    repayForm.submit('post', repay({ loan: props.loan.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showRepayModal.value = false;
            repayForm.form.reset();
        },
    });
};

const handleWaiveLoan = () => {
    if (confirm(`Are you sure you want to waive the remaining debt for Loan #${props.loan.id}? This action cannot be undone.`)) {
        router.post(waive({ loan: props.loan.id }), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <div class="p-5 space-y-6 max-w-5xl mx-auto">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <Button as="link" :href="loansIndex()" color="neutral" ghost size="sm">
                <ArrowLeft class="size-4 me-1" /> Back to Loans List
            </Button>

            <div class="flex items-center gap-2">
                <Button 
                    v-if="!is_lender && ['active', 'partial'].includes(loan.status)" 
                    color="success" 
                    size="sm" 
                    @click="openRepayModal"
                >
                    <Banknote class="size-4 me-1" /> Repay Debt
                </Button>

                <Button 
                    v-if="is_lender && ['active', 'partial'].includes(loan.status)" 
                    color="warning" 
                    soft 
                    size="sm" 
                    @click="handleWaiveLoan"
                >
                    Waive Remaining Debt
                </Button>
            </div>
        </div>

        <!-- Main Loan Detail Card -->
        <div class="card bg-base-100 shadow-md border border-base-300">
            <div class="card-body p-6 space-y-6">

                <!-- Header info -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-base-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-extrabold text-base-content">Loan #{{ loan.id }}</h1>
                            <span class="badge badge-lg uppercase font-bold" :class="{
                                'badge-primary': loan.status === 'active',
                                'badge-warning': loan.status === 'partial',
                                'badge-success': loan.status === 'paid',
                                'badge-neutral': loan.status === 'waived',
                            }">
                                {{ loan.status }}
                            </span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">
                            Disbursed on {{ loan.created_at?.formatted }}
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="text-xs text-base-content/60 block">Outstanding Debt</span>
                        <span class="text-2xl font-black text-error">
                            {{ loan.outstanding_amount?.formatted ?? loan.outstanding_amount }} BDT
                        </span>
                    </div>
                </div>

                <!-- 4-Metric Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-base-200 p-4 rounded-box">
                        <span class="text-xs text-base-content/60 block">Principal Amount</span>
                        <span class="text-lg font-bold text-base-content">{{ loan.principal_amount?.formatted }} BDT</span>
                    </div>

                    <div class="bg-base-200 p-4 rounded-box">
                        <span class="text-xs text-base-content/60 block">Lender (Disburser)</span>
                        <span class="text-sm font-bold text-base-content block truncate">{{ loan.lender?.name }}</span>
                        <span class="text-xs text-base-content/60">{{ loan.lender?.email }}</span>
                    </div>

                    <div class="bg-base-200 p-4 rounded-box">
                        <span class="text-xs text-base-content/60 block">Borrower (Recipient)</span>
                        <span class="text-sm font-bold text-base-content block truncate">{{ loan.borrower?.name }}</span>
                        <span class="text-xs text-base-content/60">{{ loan.borrower?.email }}</span>
                    </div>

                    <div class="bg-base-200 p-4 rounded-box">
                        <span class="text-xs text-base-content/60 block">Repayment Due Date</span>
                        <span class="text-sm font-bold text-base-content">{{ loan.due_at?.formatted || 'No Due Date' }}</span>
                    </div>
                </div>

                <!-- Note / Agreement Details -->
                <div v-if="loan.note" class="bg-base-200/50 p-4 rounded-box border border-base-300">
                    <span class="text-xs font-bold text-base-content/60 block mb-1">Agreement Note / Reference</span>
                    <p class="text-sm text-base-content/80 italic">{{ loan.note }}</p>
                </div>

            </div>
        </div>

        <!-- Repayment History Table -->
        <div class="card bg-base-100 shadow-md border border-base-300">
            <div class="card-body p-6 space-y-4">
                <h3 class="text-lg font-bold text-base-content flex items-center gap-2">
                    <FileText class="size-5 text-primary" />
                    <span>Repayment Ledger & History</span>
                </h3>

                <div class="overflow-x-auto">
                    <DataTable :value="loan.repayments || []" tableStyle="min-width: 40rem" class="bg-base-100">
                        <Column field="amount" header="Repayment Amount">
                            <template #body="slotProps">
                                <span class="font-extrabold text-success">
                                    +{{ slotProps.data.amount?.formatted ?? slotProps.data.amount }} BDT
                                </span>
                            </template>
                        </Column>
                        <Column field="created_at" header="Payment Date">
                            <template #body="slotProps">
                                <span class="text-xs text-base-content/70">
                                    {{ slotProps.data.created_at?.formatted }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Transaction Reference">
                            <template #body="slotProps">
                                <Button 
                                    v-if="slotProps.data.transaction?.id" 
                                    as="link" 
                                    :href="transactionsShow({ transaction: slotProps.data.transaction.id })" 
                                    color="secondary" 
                                    size="sm"
                                >
                                    <Eye class="size-4 me-1" /> View Receipt
                                </Button>
                                <span v-else class="text-xs text-base-content/50">Direct Settlement</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Repay Loan Modal -->
        <DialogModal :show="showRepayModal" @close="showRepayModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-success">
                    <Banknote class="size-5" />
                    <span>Repay Debt — Loan #{{ loan?.id }}</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleRepaySubmit" id="repay-form" class="space-y-4">
                    <div class="bg-base-200 p-3 rounded-md text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Outstanding Debt:</span>
                            <span class="font-extrabold text-error">{{ loan?.outstanding_amount?.formatted }} BDT</span>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="repay_amount" value="Repayment Amount (BDT)" />
                        <TextInput 
                            id="repay_amount" 
                            type="number" 
                            min="1" 
                            v-model="repayForm.form.amount" 
                            class="w-full mt-1 font-bold text-lg"
                            placeholder="Enter repayment amount in Taka" 
                            required 
                        />
                        <InputError :message="repayForm.form.errors.amount" class="mt-1" />
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showRepayModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    color="success" 
                    :disabled="repayForm.form.processing" 
                    type="submit" 
                    form="repay-form"
                >
                    Confirm Repayment
                </Button>
            </template>
        </DialogModal>

    </div>
</template>
