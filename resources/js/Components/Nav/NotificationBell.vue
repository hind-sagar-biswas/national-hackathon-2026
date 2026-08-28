<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useEchoNotification } from '@laravel/echo-vue';
import { Bell } from 'lucide-vue-next';
import { useToast } from 'primevue/usetoast';
import { computed } from 'vue';
import notifications from '@/routes/notifications';

const page = usePage();
const toast = useToast();

const user = page.props.auth?.user;
const unreadCount = computed(() => page.props.notifications ?? 0);

if (user?.id) {
    useEchoNotification(
        `App.Models.User.${user.id}`,
        (notification) => {
            if (typeof page.props.notifications === 'number') {
                page.props.notifications++;
            } else {
                page.props.notifications = 1;
            }

            console.log(notification);

            toast.add({
                severity: notification.severity || 'info',
                summary: notification.title || 'New Notification',
                detail: notification.message,
                life: 5000,
            });
        },
    );
}

</script>

<template>
    <div class="relative" v-if="user">
        <Link :href="notifications.index()"
            class="btn btn-ghost btn-circle relative transition-all duration-300 hover:scale-105"
            aria-label="Notifications">
            <Bell class="size-6 text-base-content/80 hover:text-primary transition-colors" />
            <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span
                    class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white items-center justify-center font-bold">
                    {{ unreadCount }}
                </span>
            </span>
        </Link>
    </div>
</template>
