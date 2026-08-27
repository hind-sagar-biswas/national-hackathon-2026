<script setup>
import { useAuth } from '@/Composables';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { toggle } from '@/routes/users';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roleAccount: Object,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: `${props.user.name}'s Profile` }],
});

const { can } = useAuth();


const toggleUserStatus = (user) => {
    if (!can('toggle-users')) return;

    router.patch(toggle({ user: user.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-5">
        <div class="card bg-base-200 mb-6">
            <div class="card-body">
                <div class="flex gap-8 items-center">
                    <div class="avatar">
                        <div class="w-20 rounded-full">
                            <img :src="user.profile_photo_url" alt="User Avatar" />
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col justify-between">
                        <p class="text-xl font-bold">{{ user.name }}</p>
                        <p class="text-gray-500 text-lg flex items-center gap-2">
                            <span v-for="role in user.role" :key="role" class="capitalize">
                                {{ role }}
                            </span>
                            <span class="badge badge-sm" :class="{
                                'badge-success': user.is_active,
                                'badge-error': !user.is_active,
                            }">{{ user.is_active ? 'Active' : 'Banned' }}</span>
                        </p>
                    </div>
                    <div>
                        <input v-if="can('toggle-users')" type="checkbox" :checked="user.is_active"
                            class="toggle toggle-success" @change="() => toggleUserStatus(user)" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-box border p-5 lg:p-6 bg-base-200">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h4 class="text-lg font-semibold mb-6">
                        Personal Information
                    </h4>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-base-content/70">
                                Name
                            </p>
                            <p class="text-sm font-medium">
                                {{ user.name }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-base-content/70">
                                Roles
                            </p>
                            <p class="text-sm font-medium">
                                <span v-for="role in user.role" :key="role" class="capitalize">
                                    {{ role }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-base-content/70">
                                Email address
                            </p>
                            <p class="text-sm font-medium">
                                {{ user.email }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-base-content/70">
                                Phone
                            </p>
                            <p class="text-sm font-medium">
                                {{ user.phone ?? '--' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-base-content/70">
                                Joined at
                            </p>
                            <p class="text-sm font-medium">
                                {{ user.created_at }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>