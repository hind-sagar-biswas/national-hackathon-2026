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
import { index, repay, show as loansShow, store, waive } from '@/routes/loans';
import { router, usePage } from '@inertiajs/vue3';
import { 
    ArrowUpRight, 
    Banknote, 
    Calendar, 
    CheckCircle2, 
    Eye, 
    HandCoins, 
    PlusCircle, 
    Receipt, 
    ShieldAlert, 
    UserCheck 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    list: Object,
    filters: Object,
    tab: String,
    stats: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'P2P Loans' }],
});

const { user } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), {
    tab: props.tab || 'given',
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

// Modals state
const showDisburseModal = ref(false);
const showRepayModal = ref(false);
const selectedLoanToRepay = ref(null);

// Forms
const disburseForm = useIdempotentForm({
    borrower: '',
    principal_amount: '',
    due_at: '',
    note: '',
});

const repayForm = useIdempotentForm({
    amount: '',
});

const handleDisburseSubmit = () => {
    disburseForm.submit('post', store(), {
        preserveScroll: true,
        onSuccess: () => {
            showDisburseModal.value = false;
            disburseForm.form.reset();
        },
    });
};

const openRepayModal = (loan) => {
    selectedLoanToRepay.value = loan;
    repayForm.resetKey();
    repayForm.form.reset();
    repayForm.form.amount = loan.outstanding_amount?.raw || loan.outstanding_amount;
    showRepayModal.value = true;
};

const handleRepaySubmit = () => {
    if (!selectedLoanToRepay.value) return;

    repayForm.submit('post', repay({ loan: selectedLoanToRepay.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showRepayModal.value = false;
            repayForm.form.reset();
        },
    });
};

const handleWaiveLoan = (loan) => {
    if (confirm(`Are you sure you want to waive the remaining debt for Loan #${loan.id}? This action cannot be undone.`)) {
        router.post(waive({ loan: loan.id }), {}, { preserveScroll: true });
    }
};

const openDisburseModal = () => {
    disburseForm.resetKey();
    disburseForm.form.reset();
    showDisburseModal.value = true;
};
</script>

<template>
    <div class="p-5 space-y-6">

        <!-- Top Header & Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card bg-base-200 shadow-sm border border-base-300 md:col-span-2">
                <div class="card-body p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center">
                            <HandCoins size="24" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-base-content">
                                Peer-to-Peer Loans & Credit
                            </h1>
                            <p class="text-xs text-base-content/60">
                                Issue micro-loans, track active debts, and process repayments seamlessly.
                            </p>
                        </div>
                    </div>

                    <Button color="primary" @click="openDisburseModal">
                        <PlusCircle class="inline-block me-1" size="16" /> Disburse Loan
                    </Button>
                </div>
            </div>

            <!-- Stats Summary Card -->
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-center text-xs text-base-content/70">
                        <span>Total Active Lent</span>
                        <span class="font-bold text-success">{{ stats?.total_lent_active?.formatted ?? stats?.total_lent_active ?? 0 }} BDT</span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-base-content/70 mt-2">
                        <span>Total Active Borrowed</span>
                        <span class="font-bold text-warning">{{ stats?.total_borrowed_active?.formatted ?? stats?.total_borrowed_active ?? 0 }} BDT</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Controls (Given vs Taken) -->
        <div class="tabs tabs-lift">
            <button 
                class="tab text-sm font-bold" 
                :class="{ 'tab-active': filters.tab === 'given' }" 
                @click="filters.tab = 'given'"
            >
                Loans Given (Lender)
            </button>
            <button 
                class="tab text-sm font-bold" 
                :class="{ 'tab-active': filters.tab === 'taken' }" 
                @click="filters.tab = 'taken'"
            >
                Loans Taken (Borrower)
            </button>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-4"
                    placeholder="Search loans by ID or reference..." />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="[
                        { label: 'Active', value: 'active' },
                        { label: 'Partial', value: 'partial' },
                        { label: 'Paid', value: 'paid' },
                        { label: 'Waived', value: 'waived' },
                    ]" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Loans DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 50rem" class="bg-base-100">
                    <Column field="id" header="Loan ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-primary">
                                #{{ slotProps.data.id }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Participant">
                        <template #body="slotProps">
                            <span class="text-sm font-medium">
                                {{ filters.tab === 'given' ? (slotProps.data.borrower?.name || 'Borrower') : (slotProps.data.lender?.name || 'Lender') }}
                            </span>
                        </template>
                    </Column>
                    <Column field="principal_amount" header="Principal">
                        <template #body="slotProps">
                            <span class="font-semibold text-base-content">
                                {{ slotProps.data.principal_amount?.formatted ?? slotProps.data.principal_amount }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column field="outstanding_amount" header="Outstanding">
                        <template #body="slotProps">
                            <span class="font-extrabold" :class="slotProps.data.outstanding_amount?.raw > 0 ? 'text-error' : 'text-success'">
                                {{ slotProps.data.outstanding_amount?.formatted ?? slotProps.data.outstanding_amount }} BDT
                            </span>
                        </template>
                    </Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span class="badge badge-sm uppercase font-bold" :class="{
                                'badge-primary': slotProps.data.status === 'active',
                                'badge-warning': slotProps.data.status === 'partial',
                                'badge-success': slotProps.data.status === 'paid',
                                'badge-neutral': slotProps.data.status === 'waived',
                            }">
                                {{ slotProps.data.status }}
                            </span>
                        </template>
                    </Column>
                    <Column field="due_at" header="Due Date">
                        <template #body="slotProps">
                            <span class="text-xs text-base-content/70">
                                {{ slotProps.data.due_at?.formatted || 'No Due Date' }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex items-center gap-1">
                                <Button as="link" :href="loansShow({ loan: slotProps.data.id })" color="secondary" size="sm" title="View Details">
                                    <Eye class="size-4" />
                                </Button>

                                <Button 
                                    v-if="filters.tab === 'taken' && ['active', 'partial'].includes(slotProps.data.status)" 
                                    color="success" 
                                    size="sm" 
                                    @click="openRepayModal(slotProps.data)"
                                    title="Repay Loan"
                                >
                                    Repay
                                </Button>

                                <Button 
                                    v-if="filters.tab === 'given' && ['active', 'partial'].includes(slotProps.data.status)" 
                                    color="warning" 
                                    soft 
                                    size="sm" 
                                    @click="handleWaiveLoan(slotProps.data)"
                                    title="Waive Debt"
                                >
                                    Waive
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </ListLayout>

        <!-- Disburse Loan Modal -->
        <DialogModal :show="showDisburseModal" @close="showDisburseModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-primary">
                    <HandCoins class="size-5" />
                    <span>Disburse New Loan</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleDisburseSubmit" id="disburse-form" class="space-y-4">
                    <!-- Borrower -->
                    <div>
                        <InputLabel for="borrower" value="Borrower (Email or Phone)" />
                        <TextInput 
                            id="borrower" 
                            type="text" 
                            v-model="disburseForm.form.borrower" 
                            class="w-full mt-1"
                            placeholder="borrower@example.com or +8801700000000" 
                            required 
                        />
                        <InputError :message="disburseForm.form.errors.borrower" class="mt-1" />
                    </div>

                    <!-- Principal Amount -->
                    <div>
                        <InputLabel for="principal_amount" value="Loan Principal Amount (BDT)" />
                        <TextInput 
                            id="principal_amount" 
                            type="number" 
                            min="1" 
                            v-model="disburseForm.form.principal_amount" 
                            class="w-full mt-1"
                            placeholder="Enter principal amount" 
                            required 
                        />
                        <InputError :message="disburseForm.form.errors.principal_amount" class="mt-1" />
                    </div>

                    <!-- Due Date -->
                    <div>
                        <InputLabel for="due_at" value="Repayment Due Date (Optional)" />
                        <TextInput 
                            id="due_at" 
                            type="date" 
                            v-model="disburseForm.form.due_at" 
                            class="w-full mt-1"
                        />
                        <InputError :message="disburseForm.form.errors.due_at" class="mt-1" />
                    </div>

                    <!-- Note -->
                    <div>
                        <InputLabel for="note" value="Note / Agreement Reference (Optional)" />
                        <TextInput 
                            id="note" 
                            type="text" 
                            v-model="disburseForm.form.note" 
                            class="w-full mt-1"
                            placeholder="e.g. Business expansion loan, emergency credit" 
                        />
                        <InputError :message="disburseForm.form.errors.note" class="mt-1" />
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showDisburseModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    color="primary" 
                    :disabled="disburseForm.form.processing" 
                    type="submit" 
                    form="disburse-form"
                >
                    Disburse Loan Funds
                </Button>
            </template>
        </DialogModal>

        <!-- Repay Loan Modal -->
        <DialogModal :show="showRepayModal" @close="showRepayModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-success">
                    <Banknote class="size-5" />
                    <span>Repay Debt — Loan #{{ selectedLoanToRepay?.id }}</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleRepaySubmit" id="repay-form" class="space-y-4">
                    <div class="bg-base-200 p-3 rounded-md text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Outstanding Balance:</span>
                            <span class="font-extrabold text-error">{{ selectedLoanToRepay?.outstanding_amount?.formatted }} BDT</span>
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
                            placeholder="Enter repayment amount" 
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
