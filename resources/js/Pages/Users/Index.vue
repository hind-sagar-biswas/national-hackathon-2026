<script setup>
import Button from '@/Components/Buttons/Button.vue';
import { useAuth, useFilter } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { index, show, toggle } from '@/routes/users';
import { Eye, SquareArrowOutUpRight } from 'lucide-vue-next';
import { Column, DataTable, Skeleton } from 'primevue';
import { router } from '@inertiajs/vue3';
import SearchBox from '@/Components/Forms/SearchBox.vue';
import SelectBox from '@/Components/Forms/SelectBox.vue';


const props = defineProps({
    list: Object,
    filters: Object,
    roleOptions: Object,
})

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Users' }],
})

const { can } = useAuth();
const { filters, reset } = useFilter(index(), props.filters, {
    debounceMs: { search: 500 },
});

const toggleUserStatus = (user) => {
    if (!can('toggle-users')) return;

    router.patch(toggle({ user: user.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <ListLayout :list="list">
        <template #filter>
            <SearchBox v-model="filters.search" class="col-span-1 md:col-span-3"
                placeholder="Search by customer name or phone..." />
            <SelectBox v-model="filters.is_active" placeholder="Status" :options="[
                { label: 'Active', value: '1' },
                { label: 'Inactive', value: '0' },
            ]" />
            <SelectBox v-model="filters.role" placeholder="Role" :options="roleOptions" />
            <Button color="accent" @click="reset" class="col-span-1 w-full">
                Reset
            </Button>
        </template>

        <div class="overflow-x-auto shadow-md rounded-md">
            <DataTable :value="list.data" tableStyle="min-width: 50rem" class="bg-base-100">
                <Column header="Avatar">
                    <template #body="slotProps">
                        <img :src="slotProps.data.profile_photo_url" :alt="slotProps.data.name"
                            class="size-10 object-cover rounded-full" />
                    </template>
                </Column>
                <Column field="name" header="Name" />
                <Column field="email" header="Email">
                    <template #body="slotProps">
                        <a :href="'mailto:' + slotProps.data.email" class="link link-primary">
                            {{ slotProps.data.email }}
                            <SquareArrowOutUpRight class="inline-block ms-1" size="16" />
                        </a>
                    </template>
                </Column>
                <Column field="roles" header="Roles">
                    <template #body="slotProps">
                        <span v-for="role in slotProps.data.role" :key="role" class="badge badge-sm badge-neutral">
                            {{ role }}
                        </span>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="slotProps">
                        <span class="badge badge-sm" :class="{
                            'badge-success': slotProps.data.is_active,
                            'badge-error': !slotProps.data.is_active
                        }">
                            {{ slotProps.data.is_active ? 'Active' : 'Banned' }}
                        </span>
                    </template>
                </Column>
                <Column field="created_at" header="Joined" />
                <Column field="view" header="View">
                    <template #body="slotProps">
                        <Button as="link" :href="show({ user: slotProps.data.id })" color="secondary" size="sm">
                            <Eye class="inline-block" size="16" />
                        </Button>
                    </template>
                </Column>
                <Column field="toggle" header="Toggle" v-if="can('toggle-users')">
                    <template #body="slotProps">
                        <input type="checkbox" :checked="slotProps.data.is_active" class="toggle toggle-success"
                            @change="() => toggleUserStatus(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

    </ListLayout>
</template>