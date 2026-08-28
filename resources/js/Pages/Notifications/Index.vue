<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import {
    CheckCircle,
    XCircle,
    Bell,
    AlertTriangle,
    Check,
    Trash2,
    ExternalLink
} from 'lucide-vue-next';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ListLayout from '@/Layouts/ListLayout.vue';
import { useFilter } from '@/Composables';
import notifications from '@/routes/notifications';
import api from '@/routes/api';

const props = defineProps({
    list: Object,
    filter: String,
    unread_count: Number,
});

defineOptions({
    layout: (props) => [DashboardLayout, { title: 'Notifications' }],
});

const toast = useToast();

const { filters } = useFilter(notifications.index(), {
    filter: props.filter || 'all',
});

const getIconComponent = (iconName) => {
    switch (iconName) {
        case 'check-circle':
        case 'success':
            return CheckCircle;
        case 'times-circle':
        case 'x-circle':
        case 'error':
            return XCircle;
        case 'warning':
            return AlertTriangle;
        default:
            return Bell;
    }
};

const markAsRead = async (id) => {
    const routeDef = api.notifications.read(id);
    try {
        await axios({
            method: routeDef.method,
            url: routeDef.url,
        });
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Notification marked as read',
            life: 3000
        });
        router.reload({ only: ['list', 'unread_count', 'notifications'] });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to mark notification as read',
            life: 3000
        });
    }
};

const markAllAsRead = async () => {
    const routeDef = api.notifications.readAll();
    try {
        await axios({
            method: routeDef.method,
            url: routeDef.url,
        });
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'All notifications marked as read',
            life: 3000
        });
        router.reload({ only: ['list', 'unread_count', 'notifications'] });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to mark all notifications as read',
            life: 3000
        });
    }
};

const deleteNotification = async (id) => {
    const routeDef = api.notifications.destroy(id);
    try {
        await axios({
            method: routeDef.method,
            url: routeDef.url,
        });
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Notification deleted successfully',
            life: 3000
        });
        router.reload({ only: ['list', 'unread_count', 'notifications'] });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to delete notification',
            life: 3000
        });
    }
};
</script>

<template>
    <ListLayout :list="list">
        <template #filter>
            <div class="col-span-1 md:col-span-4 flex items-center">
                <div class="tabs tabs-boxed w-full md:w-auto">
                    <button class="tab font-medium transition-all"
                        :class="{ 'tab-active btn-primary': filters.filter === 'all' }" @click="filters.filter = 'all'">
                        All
                    </button>
                    <button class="tab font-medium transition-all"
                        :class="{ 'tab-active btn-primary': filters.filter === 'unread' }"
                        @click="filters.filter = 'unread'">
                        Unread
                        <span v-if="unread_count > 0" class="badge badge-sm ms-2 font-bold transition-all"
                            :class="filters.filter === 'unread' ? 'bg-primary-content text-primary' : 'badge-primary'">
                            {{ unread_count }}
                        </span>
                    </button>
                    <button class="tab font-medium transition-all"
                        :class="{ 'tab-active btn-primary': filters.filter === 'read' }"
                        @click="filters.filter = 'read'">
                        Read
                    </button>
                </div>
            </div>
            <div class="col-span-1 md:col-span-2 flex justify-end items-center">
                <button v-if="unread_count > 0" @click="markAllAsRead"
                    class="btn btn-outline btn-primary btn-sm md:btn-md w-full md:w-auto transition-all hover:scale-105">
                    Mark All as Read
                </button>
            </div>
        </template>

        <div class="space-y-4">
            <div v-for="item in list.data" :key="item.id"
                class="card bg-base-100 shadow-sm border border-base-200 transition-all duration-300 hover:shadow-md hover:scale-[1.01] hover:border-primary/20"
                :class="{ 'opacity-70 bg-base-100/60': item.is_read }">
                <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <!-- Icon Container -->
                    <div class="flex-shrink-0 p-3 rounded-2xl transition-all" :class="{
                        'bg-success/10 text-success': item.notification_type === 'success',
                        'bg-info/10 text-info': item.notification_type === 'info',
                        'bg-warning/10 text-warning': item.notification_type === 'warning',
                        'bg-error/10 text-error': item.notification_type === 'error',
                    }">
                        <component :is="getIconComponent(item.icon)" class="size-6" />
                    </div>

                    <!-- Content Area -->
                    <div class="flex-grow space-y-1 w-full">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-base text-base-content"
                                :class="{ 'font-semibold text-base-content/70': item.is_read }">
                                {{ item.title }}
                            </h3>
                            <span v-if="!item.is_read" class="badge badge-sm badge-primary font-bold">New</span>
                        </div>
                        <p class="text-sm text-base-content/80 leading-relaxed">{{ item.message }}</p>
                        <span class="text-xs text-base-content/40 block font-medium">{{ item.created_at }}</span>
                    </div>

                    <!-- Actions Area -->
                    <div class="flex items-center justify-end gap-2 flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                        <!-- Action Link -->
                        <a v-if="item.action_url" :href="item.action_url"
                            class="btn btn-sm btn-ghost text-primary gap-1 hover:bg-primary/10 transition-all">
                            <span>{{ item.action_text || 'View' }}</span>
                            <ExternalLink class="size-4" />
                        </a>

                        <!-- Mark as Read -->
                        <button v-if="!item.is_read" @click="markAsRead(item.id)"
                            class="btn btn-sm btn-circle btn-ghost text-success hover:bg-success/15 transition-all"
                            title="Mark as read">
                            <Check class="size-5" />
                        </button>

                        <!-- Delete -->
                        <button @click="deleteNotification(item.id)"
                            class="btn btn-sm btn-circle btn-ghost text-error hover:bg-error/15 transition-all"
                            title="Delete">
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ListLayout>
</template>
