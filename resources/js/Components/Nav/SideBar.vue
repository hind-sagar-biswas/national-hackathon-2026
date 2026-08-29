<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '../Logo/ApplicationMark.vue';
import DropdownLink from './DropdownLink.vue';
import SideLink from './SideLink.vue';
import { useAuth, useMonitorSize } from '@/Composables';
import { computed, ref } from 'vue';
import { dashboard, logout } from '@/routes';
import profile from '@/routes/profile';
import users from '@/routes/users';
import transfers from '@/routes/transfers';
import deposits from '@/routes/deposits';
import loans from '@/routes/loans';
import moneyRequests from '@/routes/money-requests';
import transactions from '@/routes/transactions';
import adminDeposits from '@/routes/admin/deposits';
import adminHolds from '@/routes/admin/holds';
import adminReconciliation from '@/routes/admin/reconciliation';
import adminTransactions from '@/routes/admin/transactions';

const { user, roles, hasRole, can } = useAuth();
const { isMobile } = useMonitorSize();

const isDrawerOpen = ref(!isMobile.value);

const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
};

// Define User Navigation Routes
const userNavItems = [
    { title: 'Dashboard', route: dashboard(), icon: 'gauge-high', permission: null },
    { title: 'Transfers', route: transfers.index(), icon: 'money-bill-transfer', permission: 'view-transfers' },
    { title: 'Deposits', route: deposits.index(), icon: 'wallet', permission: 'view-deposits' },
    { title: 'P2P Loans', route: loans.index(), icon: 'hand-holding-dollar', permission: 'view-loans' },
    { title: 'Money Requests', route: moneyRequests.index(), icon: 'hand-holding-hand', permission: 'view-money-requests' },
    { title: 'Transactions', route: transactions.index(), icon: 'receipt', permission: 'view-transactions' },
];

// Define Admin Navigation Routes
const adminNavItems = [
    { title: 'Admin Overview', route: dashboard(), icon: 'gauge-high', permission: null },
    { title: 'Users Management', route: users.index(), icon: 'users', permission: 'view-users' },
    { title: 'Deposit Approvals', route: adminDeposits.index(), icon: 'building-columns', permission: 'view-deposits' },
    { title: 'Risk Holds', route: adminHolds.index(), icon: 'shield-halved', permission: 'view-holds' },
    { title: 'General Ledger Audit', route: adminReconciliation.index(), icon: 'scale-balanced', permission: 'view-reconciliation' },
    { title: 'All System Transactions', route: adminTransactions.index(), icon: 'file-invoice-dollar', permission: 'view-transactions' },
];

// Dynamically select and filter navigation items based on user role and permissions
const navItems = computed(() => {
    const rawItems = hasRole('admin') ? adminNavItems : userNavItems;
    return rawItems.filter(item => !item.permission || can(item.permission));
});
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
                                <span class="text-xs text-primary uppercase font-semibold">{{ roles[0] || 'User' }}</span>
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Dynamic Role-Based Navigation Menu -->
                <div class="flex-1 overflow-y-auto">
                    <ul class="menu w-full p-0">
                        <li class="menu-title text-xs font-bold uppercase tracking-wider text-base-content/50 px-4 pt-2 pb-1">
                            {{ hasRole('admin') ? 'Admin Control Portal' : 'User Banking Services' }}
                        </li>

                        <!-- Render dynamic items using v-for -->
                        <SideLink 
                            v-for="item in navItems" 
                            :key="item.title" 
                            :route="item.route" 
                            :icon="item.icon"
                        >
                            {{ item.title }}
                        </SideLink>
                    </ul>
                </div>

                <div class="divider divider-base-200 my-1"></div>

                <!-- User Profile & Account Footer Dropdown -->
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
                                <span class="opacity-70 -mt-1 text-xs truncate max-w-[170px]">{{ user.email }}</span>
                            </span>
                        </span>
                    </button>
                    <button v-else type="button" tabindex="0" role="button"
                        class="w-full text-primary-content bg-primary hover:bg-primary-focus py-2 rounded-box cursor-pointer">
                        <span class="flex flex-col justify-center items-start px-4">
                            <span class="text-xl font-semibold">{{ user.name }}</span>
                            <span class="opacity-70 text-xs">{{ user.email }}</span>
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