<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { isCurrentRoute } from '@/Composables/routeMatch';

const props = defineProps({
    route: {
        type: Object,
        required: true
    },
    icon: String,
});

const page = usePage();

const isCurrent = computed(() => {
    return isCurrentRoute(page.url, props.route);
});
</script>

<template>
    <li class="rounded-none">
        <Link :href="route" class="py-4 font-semibold" :class="{
            'text-base-content/70 hover:bg-base-300 hover:text-base-content': !isCurrent,
            'bg-primary/10 text-base-content': isCurrent
        }">
            <span class="aspect-square bg-base-100 rounded-box shadow flex justify-center items-center w-8 h-8 mr-1"
                :class="{
                    'text-primary': isCurrent,
                }">
                <fa :icon="icon" v-if="icon" />
            </span>
            <slot />
        </Link>
    </li>
</template>
