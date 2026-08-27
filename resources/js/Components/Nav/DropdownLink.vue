<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { isCurrentRoute } from '@/Composables/routeMatch';

const props = defineProps({
    route: {
        type: Object,
        required: true
    },
    method: {
        type: String,
        default: 'get',
    }
});

const page = usePage();

const isCurrent = computed(() => {
    return isCurrentRoute(page.url, props.route);
});
</script>

<template>
    <li>
        <Link :href="route" :method="method" class="py-2" :class="{
            'hover:bg-base-300': !isCurrent,
            'bg-primary text-primary-content': isCurrent
        }">
            <slot></slot>
        </Link>
    </li>
</template>
