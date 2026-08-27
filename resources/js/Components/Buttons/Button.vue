<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    as: {
        type: String,
        default: 'button',
    },
    href: {
        type: [String, Object],
        default: null,
    },
    color: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    soft: {
        type: Boolean,
        default: false,
    },
    outline: {
        type: String,
        default: null,
    },
    ghost: {
        type: Boolean,
        default: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
    wide: {
        type: Boolean,
        default: false,
    },
    block: {
        type: Boolean,
        default: false,
    },
    shape: {
        type: String,
        default: null,
    },
});

const getColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'btn-neutral';
        case 'primary':
            return 'btn-primary';
        case 'secondary':
            return 'btn-secondary';
        case 'accent':
            return 'btn-accent';
        case 'info':
            return 'btn-info';
        case 'success':
            return 'btn-success';
        case 'warning':
            return 'btn-warning';
        case 'error':
            return 'btn-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'btn-xs';
        case 'sm':
            return 'btn-sm';
        case 'lg':
            return 'btn-lg';
        case 'xl':
            return 'btn-xl';
        default:
            return 'btn-md';
    }
};

const classes = computed(() => {
    const list = ['btn', getColorClass(props.color), getSizeClass(props.size)];

    if (props.disabled) list.push('btn-disabled');
    else if (props.active) list.push('btn-active');

    if (props.soft) list.push('btn-soft');

    switch (props.outline) {
        case 'line':
            list.push('btn-outline');
            break;
        case 'dash':
            list.push('btn-dashed');
            break;
        default:
            break;
    }

    if (props.wide) list.push('btn-wide');
    else if (props.block) list.push('btn-block');
    else if (props.shape === 'circle') list.push('btn-circle');
    else if (props.shape === 'square') list.push('btn-square');

    return list.join(' ');
});
</script>

<template>
    <button v-if="as === 'button'" :type="type" :class="classes">
        <slot />
    </button>
    <Link v-else-if="as === 'link'" :href="href" :class="classes">
    <slot />
    </Link>
</template>

