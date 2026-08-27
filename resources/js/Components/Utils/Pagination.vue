<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    pagination: Object
})
</script>

<template>
    <nav class="max-w-7xl overflow-x-auto flex sm:justify-center gap-1">
        <template v-for="link in pagination" :key="link.label">
            <Link preserve-scroll :href="link.url ?? ''" v-html="link.label" class="btn btn-sm text-xs rounded-box"
                :class="{
                    'btn-primary btn-square btn-active': link.active,
                    'btn-disabled': !link.url,
                    'btn-primary btn-soft': link.url && !link.active,
                }" />
        </template>
    </nav>
</template>