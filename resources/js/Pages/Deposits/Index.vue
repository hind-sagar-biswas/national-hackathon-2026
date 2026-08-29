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
import { index, store } from '@/routes/deposits';
import { usePage } from '@inertiajs/vue3';
import { 
    CheckCircle, 
    Clock, 
    PlusCircle, 
    QrCode, 
    Search, 
    Wallet, 
    XCircle 
} from 'lucide-vue-next';
import { Column, DataTable } from 'primevue';
import { computed, ref } from 'vue';

const props = defineProps({
    list: Object,
    filters: Object,
    providerOptions: Array,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Fund Deposits' }],
});

const { user, can } = useAuth();
const page = usePage();

const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});

const showDepositModal = ref(false);

const { form, submit, resetKey } = useIdempotentForm({
    provider: '',
    provider_ref: '',
    amount: '',
});

const handleDepositSubmit = () => {
    submit('post', store(), {
        preserveScroll: true,
        onSuccess: () => {
            showDepositModal.value = false;
            form.reset();
        },
    });
};

const openDepositModal = () => {
    resetKey();
    form.reset();
    if (props.providerOptions && props.providerOptions.length > 0) {
        form.provider = props.providerOptions[0].value;
    }
    showDepositModal.value = true;
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
                            <Wallet size="24" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-base-content">
                                Add Money / Deposit
                            </h1>
                            <p class="text-xs text-base-content/60">
                                Request instant wallet deposits via mobile banking or bank transfers.
                            </p>
                        </div>
                    </div>

                    <Button color="primary" @click="openDepositModal">
                        <PlusCircle class="inline-block me-1" size="16" /> Request Deposit
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main List Layout -->
        <ListLayout :list="list">
            <template #filter>
                <SearchBox v-model="filters.search" class="col-span-1 md:col-span-3"
                    placeholder="Search by Transaction Ref ID..." />
                
                <SelectBox v-model="filters.provider" placeholder="All Providers" class="col-span-1 md:col-span-2"
                    :options="providerOptions" />
                
                <SelectBox v-model="filters.status" placeholder="All Statuses" class="col-span-1 md:col-span-2"
                    :options="[
                        { label: 'Pending', value: 'pending' },
                        { label: 'Approved', value: 'approved' },
                        { label: 'Rejected', value: 'rejected' },
                    ]" />
                
                <Button color="accent" @click="reset" class="col-span-1 w-full">
                    Reset
                </Button>
            </template>

            <!-- Deposits DataTable -->
            <div class="overflow-x-auto shadow-md rounded-md">
                <DataTable :value="list.data" tableStyle="min-width: 50rem" class="bg-base-100">
                    <Column field="provider_ref" header="Provider Ref / Txn ID">
                        <template #body="slotProps">
                            <span class="font-mono text-xs font-semibold text-primary">
                                {{ slotProps.data.provider_ref }}
                            </span>
                        </template>
                    </Column>
                    <Column field="provider" header="Gateway / Provider">
                        <template #body="slotProps">
                            <span class="badge badge-outline uppercase font-bold">
                                {{ slotProps.data.provider }}
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
                            <span class="badge badge-sm gap-1 capitalize font-bold" :class="{
                                'badge-warning': slotProps.data.status === 'pending',
                                'badge-success': slotProps.data.status === 'approved',
                                'badge-error': slotProps.data.status === 'rejected',
                            }">
                                <Clock v-if="slotProps.data.status === 'pending'" class="size-3" />
                                <CheckCircle v-else-if="slotProps.data.status === 'approved'" class="size-3" />
                                <XCircle v-else class="size-3" />
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
                </DataTable>
            </div>
        </ListLayout>

        <!-- Request Deposit Modal -->
        <DialogModal :show="showDepositModal" @close="showDepositModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-primary">
                    <PlusCircle class="size-5" />
                    <span>Request Wallet Deposit</span>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="handleDepositSubmit" id="deposit-form" class="space-y-4">
                    <!-- Deposit Provider / Gateway -->
                    <div>
                        <InputLabel for="provider" value="Payment Provider / Gateway" />
                        <SelectBox 
                            id="provider" 
                            v-model="form.provider" 
                            :options="providerOptions" 
                            class="w-full mt-1" 
                            required 
                        />
                        <InputError :message="form.errors.provider" class="mt-1" />
                    </div>

                    <!-- Provider Reference / Transaction ID -->
                    <div>
                        <InputLabel for="provider_ref" value="Payment Reference / Trx ID" />
                        <TextInput 
                            id="provider_ref" 
                            type="text" 
                            v-model="form.provider_ref" 
                            class="w-full mt-1 font-mono"
                            placeholder="e.g. BK129083X9 or Bank Ref ID" 
                            required 
                        />
                        <InputError :message="form.errors.provider_ref" class="mt-1" />
                    </div>

                    <!-- Amount -->
                    <div>
                        <InputLabel for="amount" value="Deposit Amount (BDT)" />
                        <TextInput 
                            id="amount" 
                            type="number" 
                            min="1" 
                            v-model="form.amount" 
                            class="w-full mt-1"
                            placeholder="Enter deposit amount" 
                            required 
                        />
                        <InputError :message="form.errors.amount" class="mt-1" />
                    </div>
                </form>
            </template>

            <template #footer>
                <Button color="neutral" soft class="me-2" @click="showDepositModal = false" type="button">
                    Cancel
                </Button>

                <Button 
                    color="primary" 
                    :disabled="form.processing" 
                    type="submit" 
                    form="deposit-form"
                >
                    Submit Deposit Request
                </Button>
            </template>
        </DialogModal>

    </div>
</template>
