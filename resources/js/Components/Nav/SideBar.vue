<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '../Logo/ApplicationMark.vue';
import DropdownLink from './DropdownLink.vue';
import SideLink from './SideLink.vue';
import { useAuth, useMonitorSize } from '@/Composables';
import { ref } from 'vue';
import { dashboard, logout } from '@/routes';
import profile from '@/routes/profile';
import users from '@/routes/users';

const { user, roles, hasRole } = useAuth();

const { isMobile } = useMonitorSize();

const isDrawerOpen = ref(!isMobile.value);

const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
};
</script>

<template>
    <div class="drawer" :class="{ 'lg:drawer-open': isDrawerOpen }">
        <input id="navbar" type="checkbox" v-model="isDrawerOpen" v-shortkey="['ctrl', 'b']" @shortkey="toggleDrawer"
            class="drawer-toggle" />
        <div class="drawer-content">
            <!-- Page content here -->
            <slot />
        </div>
        <div class="drawer-side shadow">
            <label for="navbar" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="menu bg-base-200 text-base-content h-full w-80 p-0">
                <!-- Logo -->
                <div class="bg-base-100 pt-4 pb-6">
                    <div class="w-full mt-2">
                        <Link :href="dashboard()" class="w-full flex items-center gap-4 px-4">
                            <ApplicationMark class="block h-11 w-auto" />
                            <span class="flex flex-col justify-center items-start">
                                <span class="uppercase font-bold font-bills text-2xl">{{ $page.props.site.name }}</span>
                                <span class="text-xs text-primary uppercase">{{ roles[0] }}</span>
                            </span>
                        </Link>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <ul class="menu w-full p-0">
                        <!-- Sidebar content here -->
                        <SideLink :route="dashboard()" icon="gauge-high">Dashboard</SideLink>
                        <SideLink v-if="hasRole('admin')" :route="users.index()" icon="users">Users</SideLink>
                    </ul>
                </div>
                <div class="divider divider-base-200"></div>
                <div class="dropdown dropdown-top mb-4 mx-4">
                    <button v-if="$page.props.jetstream.managesProfilePhotos" tabindex="0" role="button"
                        class="w-full bg-base-200 hover:bg-base-300 py-2 rounded-box cursor-pointer">
                        <span class="w-full flex items-center gap-4 px-4">
                            <span
                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-hidden focus:border-gray-300 transition">
                                <img class="size-10 rounded-full object-cover" :src="user.profile_photo_url"
                                    :alt="user.name">
                            </span>
                            <span class="flex flex-col justify-center items-start">
                                <span class="text-lg font-semibold">{{ user.name }}</span>
                                <span class="opacity-70 -mt-1">{{ user.email }}</span>
                            </span>
                        </span>
                    </button>
                    <button v-else type="button" tabindex="0" role="button"
                        class="w-full text-primary-content bg-primary hover:bg-primary-focus py-2 rounded-box cursor-pointer">
                        <span class="flex flex-col justify-center items-start px-4">
                            <span class="text-xl font-semibold">{{ user.name }}</span>
                            <span class="opacity-70">{{ user.email }}</span>
                        </span>
                    </button>
                    <ul tabindex="-1" class="dropdown-content menu bg-base-200 rounded-box z-1 w-full p-2 shadow-sm">
                        <DropdownLink :route="profile.show()">Profile</DropdownLink>
                        <div class="border-t border-gray-200" />
                        <DropdownLink :route="logout()" method="post">Log Out</DropdownLink>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>