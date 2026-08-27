<script setup>
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import Layout from '@/Layouts/DashboardLayout.vue';

defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});

defineOptions({
    layout: [Layout, { title: 'API Tokens' }],
})
</script>

<template>
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <ApiTokenManager :tokens="tokens" :available-permissions="availablePermissions"
                :default-permissions="defaultPermissions" />
        </div>
    </div>
</template>
