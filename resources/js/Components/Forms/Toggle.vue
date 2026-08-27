<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    color: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});

const getColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'toggle-neutral';
        case 'primary':
            return 'toggle-primary';
        case 'secondary':
            return 'toggle-secondary';
        case 'accent':
            return 'toggle-accent';
        case 'info':
            return 'toggle-info';
        case 'success':
            return 'toggle-success';
        case 'warning':
            return 'toggle-warning';
        case 'error':
            return 'toggle-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'toggle-xs';
        case 'sm':
            return 'toggle-sm';
        case 'lg':
            return 'toggle-lg';
        case 'xl':
            return 'toggle-xl';
        default:
            return 'toggle-md';
    }
};

const classes = computed(() => ['toggle', getColorClass(props.color), getSizeClass(props.size)].join(' '));
</script>

<template>
    <input type="checkbox" v-model="proxyChecked" :class="classes" />
</template>
