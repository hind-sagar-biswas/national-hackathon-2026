<script setup>
import ApplicationMark from '@/Components/Logo/ApplicationMark.vue';
import { useAuth } from '@/Composables';
import { dashboard, login, logout, register, welcome } from '@/routes';
import { show as profileShow } from '@/routes/profile';
import { Head, Link } from '@inertiajs/vue3';
import { UserIcon } from 'lucide-vue-next';
import Toast from 'primevue/toast';
import NotificationBell from '@/Components/Nav/NotificationBell.vue';

const { user } = useAuth();

defineProps({
    title: String,
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>

    <Head title="Welcome" />
    <div class="flex flex-col min-h-screen bg-base-200">
        <Toast />
        <div class="navbar bg-base-100 shadow-sm">
            <div class="flex-1">
                <Link :href="welcome()" class="flex items-center font-semibold px-5 text-xl text-primary">
                    <ApplicationMark class="size-8 me-2" />
                    {{ $page.props.site.name }}
                </Link>
            </div>
            <div class="flex gap-2 items-center">
                <NotificationBell v-if="$page.props.auth.user" />
                <div class="dropdown dropdown-end mr-2" v-if="$page.props.auth.user">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar"
                        v-if="$page.props.jetstream.managesProfilePhotos">
                        <div class="w-10 rounded-full">
                            <img class="object-cover" :src="user.profile_photo_url" :alt="user.name">
                        </div>
                    </div>
                    <button v-else class="btn btn-soft btn-primary">
                        <UserIcon class="size-5" />
                        {{ user.name }}
                    </button>
                    <ul tabindex="-1"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                        <li>
                            <Link :href="dashboard()">Dashboard</Link>
                        </li>
                        <li>
                            <Link :href="profileShow()">Settings</Link>
                        </li>
                        <li>
                            <Link :href="logout()" method="POST">Logout</Link>
                        </li>
                    </ul>
                </div>
                <ul class="menu menu-horizontal px-1 gap-1" v-else>
                    <li v-if="canLogin">
                        <Link :href="login()" class="btn btn-primary btn-soft">Log in</Link>
                    </li>
                    <li v-if="canRegister">
                        <Link :href="register()" class="btn btn-primary">Register</Link>
                    </li>
                </ul>
            </div>
        </div>
        <main class="flex-1">
            <slot />
        </main>
        <footer class="footer sm:footer-horizontal footer-center bg-base-300 text-base-content p-4">
            <aside>
                <p>Copyright © {{ new Date().getFullYear() }} - All right reserved by {{ $page.props.site.name }}</p>
            </aside>
        </footer>
    </div>
</template>